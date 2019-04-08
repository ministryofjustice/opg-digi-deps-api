<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\User;
use Tests\Fixtures;

class StatsControllerTest extends AbstractTestController
{
    // users
    private static $tokenAdmin;

    public function setUp()
    {
        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
        }
    }

    public function testOutputForFixtures()
    {
        $data = $this->assertJsonRequest('GET', '/stats', [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenAdmin,
        ])['data'];

        $this->assertEquals($data['reports_count'], 0);
        $this->assertEquals($data['pa_named_deputy_count'], 2);
        $this->assertEquals($data['prof_named_deputy_count'], 2);
    }

    public function testOutputWithExtraData()
    {
        // Add new report
        $client = new Client();
        $report = new Report($client, Report::TYPE_102, new \DateTime(), new \DateTime(), false);
        $report->setSubmitted(true);
        $report->setSubmitDate(new \DateTime());
        $this->fixtures()->persist($client);
        $this->fixtures()->persist($report);

        // Add named PA deputy
        $paUser = new User();
        $paUser->setFirstname('Test');
        $paUser->setEmail('pa-test@example.org');
        $paUser->setRegistrationDate(new \DateTime());
        $paUser->setRoleName('ROLE_PA_NAMED');
        $this->fixtures()->persist($paUser);

        // Add prof team deputy
        $profUser = new User();
        $profUser->setFirstname('Test');
        $profUser->setEmail('prof-test@example.org');
        $profUser->setRegistrationDate(new \DateTime());
        $profUser->setRoleName('ROLE_PROF_ADMIN');
        $this->fixtures()->persist($profUser);

        $this->fixtures()->flush();

        $data = $this->assertJsonRequest('GET', '/stats', [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenAdmin,
        ])['data'];

        $this->assertEquals(1, $data['reports_count']);
        $this->assertEquals(3, $data['pa_named_deputy_count']);
        $this->assertEquals(2, $data['prof_named_deputy_count']);

        // Remove test data
        $this->fixtures()->remove($profUser);
        $this->fixtures()->remove($paUser);
        $this->fixtures()->remove($report);
        $this->fixtures()->remove($client);
        $this->fixtures()->flush();
    }
}
