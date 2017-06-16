<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\Note;
use Symfony\Component\Validator\Constraints\DateTime;
use Tests\AppBundle\Controller\AbstractTestController;

class NoteControllerTest extends AbstractTestController
{

    // users
    private static $tokenDeputy;
    private static $tokenAdmin;
    private static $tokenPa;
    private static $tokenPa2;
    private static $tokenPa3;

    // lay
    private static $deputy1;
    private static $client1;

    // pa
    private static $pa1;
    private static $pa1Client1;
    private static $pa1Client1Note1;
    private static $pa1Client2;
    private static $pa2;
    private static $pa3;
    private static $pa3Client1;


    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        //deputy1
        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$client1 = self::fixtures()->createClient(self::$deputy1, ['setFirstname' => 'c1']);

        // pa 1
        self::$pa1 = self::fixtures()->getRepo('User')->findOneByEmail('pa@example.org');
        self::$pa1Client1 = self::fixtures()->createClient(self::$pa1, ['setFirstname' => 'pa1Client1']);
        self::$pa1Client1Note1 = self::fixtures()->createNote(self::$pa1Client1, self::$pa1, 'cat', 'title', 'content');
        self::$pa1Client2 = self::fixtures()->createClient(self::$pa1, ['setFirstname' => 'pa1Client2']);
        // pa2 (same team as pa1)
        self::$pa2 = self::fixtures()->getRepo('User')->findOneByEmail('pa_admin@example.org')->addClient(self::$pa1Client1);

        // pa 3 with other client (other team)
        self::$pa3 = self::fixtures()->getRepo('User')->findOneByEmail('pa_team_member@example.org');
        self::$pa3Client1 = self::fixtures()->createClient(self::$pa3, ['setFirstname' => 'pa2Client1']);

        self::fixtures()->flush()->clear();
    }

    /**
     * clear fixtures.
     */
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        self::fixtures()->clear();
    }


    public function setUp()
    {
        if (null === self::$tokenAdmin) {
            self::$tokenDeputy = $this->loginAsDeputy();
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenPa = $this->loginAsPa();
            self::$tokenPa2 = $this->loginAsPaAdmin();
            self::$tokenPa3 = $this->loginAsPaTeamMember();
        }
    }

    public function testAdd()
    {
        $this->markTestIncomplete('needs endpoint to rely on client Id first');
    }


    public function testgetOneById()
    {
        $url = '/note/' . self::$pa1Client1Note1->getId();

        // assert Auth and ACL
        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
        $this->assertEndpointAllowedFor('GET', $url, self::$tokenPa2);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenPa3);

        // assert get
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenPa,
        ])['data'];

        $this->assertEquals(self::$pa1Client1Note1->getId(), $data['id']);
        $this->assertEquals('cat', $data['category']);
        $this->assertEquals('title', $data['title']);
        $this->assertEquals('content', $data['content']);
        $this->assertEquals(self::$pa1->getId(), $data['created_by']['id']);
        $this->assertEquals(true, time() - strtotime($data['created_on']) < 3600);

    }

    public function testupdateNote()
    {
        $url = '/note/' . self::$pa1Client1Note1->getId();

        // assert Auth
        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenPa2);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenPa3);

        // assert PUT
        $data = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenPa,
            'data'        => [
                'category'     => 'cat-edited',
                'title'   => 'title-edited',
                'content' => 'content-edited',
            ],
        ])['data'];

        $this->assertEquals(self::$pa1Client1Note1->getId(), $data['id']);
        $this->assertEquals('cat-edited', $data['category']);
        $this->assertEquals('title-edited', $data['title']);
        $this->assertEquals('content-edited', $data['content']);
        $this->assertEquals(true, time() - strtotime($data['created_on']) < 3600);

        //assert cannot change others' notes
        // assert PUT
        $data = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => false,
            'AuthToken'   => self::$tokenPa2,
            'data'        => [
                'category'     => 'cat-edited2',
                'title'   => 'title-edited2',
                'content' => 'content-edited2',
            ],
        ])['data'];
    }

}