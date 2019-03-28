<?php

namespace Tests\AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\Fixtures;

abstract class AbstractTestController extends WebTestCase
{
    /**
     * @var Fixtures
     */
    protected static $fixtures;

    /**
     * @var Client
     */
    protected static $frameworkBundleClient;

    /**
     * Create static client and fixtures.
     */
    public static function setUpBeforeClass()
    {
        // each test restores the db before launching the entire suite,
        // help to cleanup records created from previously-executed tests
        //TODO consider moving into setUpBeforeClass of each method. might not be needed for some tests
        Fixtures::deleteReportsData();

        self::$frameworkBundleClient = static::createClient(['environment' => 'test', 'debug' => false, ]);
        $em = self::$frameworkBundleClient->getContainer()->get('em');

        $t = self::$frameworkBundleClient->getContainer()->getParameter('fixtures');

        self::$fixtures = new Fixtures($em);
        $em->clear();

        unset($em);
    }

    /**
     * clear fixtures.
     */
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
    }

    /**
     * @return Fixtures
     */
    public static function fixtures()
    {
        return self::$fixtures;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return self::$frameworkBundleClient;
    }

    /**
     * @param array $options with keys method, uri, data, mustSucceed, mustFail, assertId
     *
     * @return type
     */
    public function assertJsonRequest($method, $uri, array $options = [])
    {
        $headers = ['CONTENT_TYPE' => 'application/json'];
        if (isset($options['AuthToken'])) {
            $headers['HTTP_AuthToken'] = $options['AuthToken'];
        }
        if (isset($options['ClientSecret'])) {
            $headers['HTTP_ClientSecret'] = $options['ClientSecret'];
        }

        $rawData = null;
        if (isset($options['data'])) {
            $rawData = json_encode($options['data']);
        } elseif (isset($options['rawData'])) {
            $rawData = $options['rawData'];
        }

        self::$frameworkBundleClient->request(
            $method,
            $uri,
            [], [],
            $headers,
           $rawData
        );
        $response = self::$frameworkBundleClient->getResponse();

        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'), 'wrong content type. Headers: ' . $response->headers);
        $return = json_decode($response->getContent(), true);
        $this->assertNotEmpty($return, 'Response not json');
        if (!empty($options['mustSucceed'])) {
            $this->assertTrue($return['success'], "Endpoint didn't succeed as expected. Response: " . print_r($return, true));
            if (!empty($options['assertId'])) {
                $this->assertTrue($return['data']['id'] > 0);
            }
        }
        if (!empty($options['mustFail'])) {
            $this->assertFalse($return['success'], "Endpoint didn't fail as expected. Response: " . print_r($return, true));
        }
        if (!empty($options['assertCode'])) {
            $this->assertEquals($options['assertResponseCode'], $return['code'], 'Response: ' . print_r($return, true));
        }
        if (!empty($options['assertResponseCode'])) {
            $this->assertEquals($options['assertResponseCode'], $response->getStatusCode(), 'Response: ' . $response->getStatusCode() . print_r($return, true));
        }

        return $return;
    }

    /**
     * @param string $email
     * @param string $password
     *
     * @return string token
     */
    public function login($email, $password, $clientSecret)
    {
        self::$frameworkBundleClient->request('GET', '/'); // warm up to get container

        // reset brute-force counters
        $key = 'email' . $email;
        self::$frameworkBundleClient->getContainer()->get('attemptsInTimeChecker')->resetAttempts($key);
        self::$frameworkBundleClient->getContainer()->get('attemptsIncrementalWaitingChecker')->resetAttempts($key);

        $responseArray = $this->assertJsonRequest('POST', '/auth/login', [
            'mustSucceed' => true,
            'ClientSecret' => $clientSecret,
            'data' => [
                'email' => $email,
                'password' => $password,
            ],
        ])['data'];
        $this->assertEquals($email, $responseArray['email']);

        // check token
        $token = self::$frameworkBundleClient->getResponse()->headers->get('AuthToken');

        return $token;
    }

    protected function assertEndpointNeedsAuth($method, $uri, $authToken = 'WRONG')
    {
        $response = $this->assertJsonRequest($method, $uri, [
            'mustFail' => true,
            'AuthToken' => $authToken,
            'assertResponseCode' => 419,
        ]);
        $this->assertEquals(419, $response['code']);
    }

    protected function assertEndpointNotAllowedFor($method, $uri, $token, $data = [])
    {
        $this->assertJsonRequest($method, $uri, [
            'mustFail' => true,
            'data' => $data,
            'AuthToken' => $token,
            'assertResponseCode' => 403,
        ]);
    }

    protected function assertEndpointAllowedFor($method, $uri, $token, $data = [])
    {
        $this->assertJsonRequest($method, $uri, [
            'mustSucceed' => true,
            'data' => $data,
            'AuthToken' => $token,
            'assertResponseCode' => 200,
        ]);
    }

    /**
     * @return string token
     */
    protected function loginAsDeputy()
    {
        return $this->login('deputy@example.org', 'Abcd1234', API_TOKEN_DEPUTY);
    }

    /**
     * @return string token
     */
    protected function loginAsPa()
    {
        return $this->login('pa@example.org', 'Abcd1234', API_TOKEN_DEPUTY);
    }

    /**
     * @return string token
     */
    protected function loginAsPaAdmin()
    {
        return $this->login('pa_admin@example.org', 'Abcd1234', API_TOKEN_DEPUTY);
    }

    /**
     * @return string token
     */
    protected function loginAsPaTeamMember()
    {
        return $this->login('pa_team_member@example.org', 'Abcd1234', API_TOKEN_DEPUTY);
    }

    /**
     * @return string token
     */
    protected function loginAsProf()
    {
        return $this->login('prof@example.org', 'Abcd1234', API_TOKEN_DEPUTY);
    }

    /**
     * @return string token
     */
    protected function loginAsAdmin()
    {
        return $this->login('admin@example.org', 'Abcd1234', API_TOKEN_ADMIN);
    }

    /**
     * @return string token
     */
    protected function loginAsCaseManager()
    {
        return $this->login('casemanager@example.org', 'Abcd1234', API_TOKEN_ADMIN);
    }

    protected function tearDown()
    {
        parent::tearDown();

        // clean up vars
        $refl = new \ReflectionObject($this);
        foreach ($refl->getProperties() as $prop) {
            if (!$prop->isStatic() && 0 !== strpos($prop->getDeclaringClass()->getName(), 'PHPUnit_')) {
                $prop->setAccessible(true);
                $prop->setValue($this, null);
            }
        }
    }
}
