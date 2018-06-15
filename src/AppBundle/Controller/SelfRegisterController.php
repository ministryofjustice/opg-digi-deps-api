<?php

namespace AppBundle\Controller;

use AppBundle\Model\SelfRegisterData;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/selfregister")
 */
class SelfRegisterController extends RestController
{
    /**
     * @Route("")
     * @Method({"POST"})
     */
    public function register(Request $request)
    {
        if (!$this->getAuthService()->isSecretValid($request)) {
            throw new \RuntimeException('client secret not accepted.', 403);
        }

        $data = $this->deserializeBodyContent($request);

        $selfRegisterData = new SelfRegisterData();

        $this->populateSelfReg($selfRegisterData, $data);

        $validator = $this->get('validator');
        $errors = $validator->validate($selfRegisterData, null, 'self_registration');

        if (count($errors) > 0) {
            throw new \RuntimeException('Invalid registration data: ' . $errors);
        }

        try {
            $user = $this->container->get('user_registration_service')->selfRegisterUser($selfRegisterData);
            $this->get('logger')->warning('CasRec register success: ', ['extra' => ['page' => 'user_registration', 'success' => true] + $selfRegisterData->toArray()]);
        } catch (\Exception $e) {
            $this->get('logger')->warning('CasRec register failed:', ['extra' => ['page' => 'user_registration', 'success' => false] + $selfRegisterData->toArray()]);
            throw $e;
        }

        $this->setJmsSerialiserGroups(['user', 'user-login']);

        return $user;
    }

    /**
     * @Route("/verifycodeputy")
     * @Method({"POST"})
     */
    public function verifyCoDeputy(Request $request)
    {
        $coDeputyVerified = false;

        if (!$this->getAuthService()->isSecretValid($request)) {
            throw new \RuntimeException('client secret not accepted.', 403);
        }

        $selfRegisterData = new SelfRegisterData();
        $this->populateSelfReg($selfRegisterData, $this->deserializeBodyContent($request));

        $validator = $this->get('validator');
        $errors = $validator->validate($selfRegisterData, ['verify_codeputy']);

        if (count($errors) > 0) {
            throw new \RuntimeException('Invalid registration data: ' . $errors);
        }

        try {
            $coDeputyVerified = $this->container->get('user_registration_service')->validateCoDeputy($selfRegisterData);
            $this->get('logger')->warning('CasRec codeputy validation success: ', ['extra' => ['page' => 'codep_validation', 'success' => true] + $selfRegisterData->toArray()]);
        } catch (\Exception $e) {
            $this->get('logger')->warning('CasRec codeputy validation failed:', ['extra' => ['page' => 'codep_validation', 'success' => false] + $selfRegisterData->toArray()]);
            throw $e;
        }

        return ['verified' => $coDeputyVerified];
    }

    /**
     * @param SelfRegisterData $selfRegisterData
     * @param array            $data
     */
    public function populateSelfReg(SelfRegisterData $selfRegisterData, array $data)
    {
        $this->hydrateEntityWithArrayData($selfRegisterData, $data, [
            'firstname' => 'setFirstname',
            'lastname' => 'setLastname',
            'email' => 'setEmail',
            'postcode' => 'setPostcode',
            'client_firstname' => 'setClientFirstname',
            'client_lastname' => 'setClientLastname',
            'case_number' => 'setCaseNumber',
        ]);
    }
}
