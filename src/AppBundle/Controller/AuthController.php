<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Exception as AppExceptions;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @Route("/auth")
 */
class AuthController extends RestController
{
    /**
     * Return the user by email and hashed password (or exception if not found)
     * 
     * 
     * @Route("/login")
     * @Method({"POST"})
     */
    public function login()
    {
        $data = $this->deserializeBodyContent([
            'email' => 'notEmpty',
            'password' => 'notEmpty',
        ]);
        
        // log the user in using symfony stuff
        $user = $this->findEntityBy('User', [
            'email'=> $data['email'],
            'password'=> $data['password']
        ], 'User not found');
        
        if (!$user) {
            throw new \RuntimeException('Cannot find user with the given username and password');
        }
        
        $randomToken = $user->getRandomTokenBasedOnInternalData();
        
        $this->get('kernel.listener.responseConverter')->addResponseModifier(function ($request) use ($randomToken) {
            $request->headers->set('AuthToken', $randomToken);
        });
        
        // manually set session token into security context (manual login)
        $token = new UsernamePasswordToken($user, null, "secured_area", $user->getRoles());
        $this->get("security.context")->setToken($token);
        
        // TODO store (tandomToken, user) into the DB
        // TODO each endpoint performs check after having read the "auth" header
        // - no token => 404
        // - get the user and perform ACL
        
        return $user;
    }
    
    /**
     * Test endpoint used for testing to check auth permissions
     * 
     * @Route("/get-logged-user")
     * @Method({"GET"})
     */
    public function test()
    {
        return $this->get('security.context')->getToken()->getUser();
    }
   
}