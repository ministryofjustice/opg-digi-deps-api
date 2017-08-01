<?php

namespace Tests\AppBundle\Controller\Report;

use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Fee;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportSubmission;
use Doctrine\Tests\ORM\Mapping\User;
use Tests\AppBundle\Controller\AbstractTestController;

class ReportSubmissionControllerTest extends AbstractTestController
{
    private static $pa1;
    private static $pa2;
    private static $deputy1;
    private static $tokenAdmin = null;
    private static $tokenDeputy = null;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$pa1 = self::fixtures()->getRepo('User')->findOneByEmail('pa@example.org');
        self::$pa2 = self::fixtures()->getRepo('User')->findOneByEmail('pa_admin@example.org');
        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');

        // create 5 submitted reports
        for($i=0; $i<5; $i++) {
            $client = self::fixtures()->createClient(
                self::$pa1,
                ['setFirstname' => "c{$i}", 'setLastname' => "l{$i}", 'setCaseNumber' => "100000{$i}"]
            );
            $report = self::fixtures()->createReport($client, [
                'setStartDate'   => new \DateTime('2014-01-01'),
                'setEndDate'     => new \DateTime('2014-12-31'),
                'setSubmitted'   => true,
                'setSubmittedBy' => ($i<3) ? self::$pa1 : self::$pa2,
            ]);
            // create submission
            $submission = new ReportSubmission($report, ($i<3) ? self::$pa1 : self::$deputy1);
            // add documents, needed for future tests
            $document = new Document($report);
            $document->setFileName('file1.pdf')->setStorageReference('storageref1')->setReportSubmission($submission);
            self::fixtures()->persist($document, $submission);
        }

        self::fixtures()->flush()->clear();
    }

    public function setUp()
    {
        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
        }
    }

    public function testGetAllWithFiltersGetOneArchive()
    {
        $reportsGetAllRequest = function (array $params = []) {
            $url = '/report-submission?' . http_build_query($params);

            return $this->assertJsonRequest('GET', $url, [
                'mustSucceed' => true,
                'AuthToken'   => self::$tokenAdmin,
            ])['data'];
        };

        $this->assertEndpointNeedsAuth('GET', '/report-submission');
        $this->assertEndpointNotAllowedFor('GET', '/report-submission', self::$tokenDeputy);

        // assert submission (only one expected)
        $data = $reportsGetAllRequest(['status'=>'new']);
        $this->assertEquals(['new'=>5, 'archived'=>0], $data['counts']);
        $submission = $data['records'][0];
        $this->assertNotEmpty($submission['id']);
        $this->assertNotEmpty($submission['report']['type']);
        $this->assertNotEmpty($submission['report']['start_date']);
        $this->assertNotEmpty($submission['report']['end_date']);
        $this->assertNotEmpty($submission['report']['client']['case_number']);
        $this->assertNotEmpty($submission['report']['client']['firstname']);
        $this->assertNotEmpty($submission['report']['client']['lastname']);
        $this->assertEquals('file1.pdf', $submission['documents'][0]['file_name']);
        $this->assertNotEmpty($submission['created_by']['firstname']);
        $this->assertNotEmpty($submission['created_by']['lastname']);
        $this->assertNotEmpty($submission['created_by']['role_name']);
        $this->assertNotEmpty($submission['created_on']);
        $this->assertArrayHasKey('archived_by', $submission);

        // test getOne endpoint
        $data = $this->assertJsonRequest('GET', '/report-submission/' . $submission['id'], [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenAdmin,
        ])['data'];
        $this->assertEquals($submission['id'], $data['id']);
        $this->assertEquals('storageref1', $data['documents'][0]['storage_reference']);

        // archive 1st submission
        $data = $this->assertJsonRequest('PUT', '/report-submission/' . $submission['id'], [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenAdmin,
            'data' => ['archive'=>true]
        ])['data'];
        $this->assertEquals($submission['id'], $data);

        // check counts after submission
        $data = $reportsGetAllRequest([]);
        $this->assertEquals(['new'=>4, 'archived'=>1], $data['counts']);


        // check filters and counts
        $data = $reportsGetAllRequest(['q'=>'1000000']);
        $this->assertEquals(['new'=>1, 'archived'=>0], $data['counts']);
        $this->assertCount(1, $data['records']);

        $data = $reportsGetAllRequest(['q'=>'1000000', 'status'=>'new']);
        $this->assertEquals(['new'=>1, 'archived'=>0], $data['counts']);
        $this->assertCount(1, $data['records']);

        $this->assertEquals(['new'=>1, 'archived'=>0], $reportsGetAllRequest(['status'=>'new', 'q'=>'c0'])['counts']); // client name
        $this->assertEquals(['new'=>1, 'archived'=>0], $reportsGetAllRequest(['status'=>'new', 'q'=>'l0'])['counts']); //client surname
        $this->assertEquals(['new'=>4, 'archived'=>1], $reportsGetAllRequest(['status'=>'new', 'q'=>'test'])['counts']); // deputy name
        $this->assertEquals(['new'=>1, 'archived'=>1], $reportsGetAllRequest(['created_by_role'=>'ROLE_LAY_DEPUTY'])['counts']);
//        $this->assertEquals(['new'=>1, 'archived'=>1], $reportsGetAllRequest(['created_by_role'=>'ROLE_PA'])['counts']);

        // check pagination and limit
        $data = $reportsGetAllRequest(['status'=>'new', 'q'=>'test'])['records'];
        $this->assertEquals('1000003', $data[0]['report']['client']['case_number']);
        $this->assertEquals('1000002', $data[1]['report']['client']['case_number']);
        $this->assertEquals('1000001', $data[2]['report']['client']['case_number']);
        $this->assertEquals('1000000', $data[3]['report']['client']['case_number']);

        $data = $reportsGetAllRequest(['status'=>'new', 'q'=>'test', 'offset'=>1, 'limit'=>2])['records'];
        $this->assertCount(2, $data);
        $this->assertEquals('1000002', $data[0]['report']['client']['case_number']);
        $this->assertEquals('1000001', $data[1]['report']['client']['case_number']);
    }

}
