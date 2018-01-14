<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\{
    Response,
    Request
};


class SecurityController extends Controller {

    /**
     * @Route("/login", name="login")
     */
    public function login(Request $request, AuthenticationUtils $authUtils) {
        // get the login error if there is one
        $error = $authUtils->getLastAuthenticationError();
        
        // last username entered by the user
        $lastUsername = $authUtils->getLastUsername();
if(!$this->getUser()){
    //echo $error->getMessage(); exit();
        return $this->render('security/login.html.twig', array(
                    'last_username' => $lastUsername,
                    'error' => $error,
        ));
    }
    return $this->redirectToRoute('listuser');
    }
    
    /**
     * @Route("/logincheck", name="logincheck")
     */    
    public function loginCheck(Request $request){
        $this->forward("\App\Controller\SecurityController::login");
    }
    /**
     * @Route("/logout", name="logout")
     */    
    public function logout(Request $request){
        
    }

}
