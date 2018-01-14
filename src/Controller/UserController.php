<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\{Response, Request};
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use App\Form\UserType;
use App\Entity\User;

class UserController extends Controller
{
    /**
     * @Route("/user/list", name="listuser")
     */
    public function listUser()
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em= $this->getDoctrine()->getRepository(\App\Entity\User::class);
        $users = $em->findAll();
        return $this->render("user/listuser.html.twig", array("page"=>"listuser", "users"=>$users));
    }
    
    /**
     * @Route("/user/add", name="adduser")
     */
    public function addUser(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        // replace this line with your own code!
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        
        $form->handleRequest($request);
        
         if ($form->isSubmitted() && $form->isValid()) {

            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $this->addFlash("registrationsuccess", "User registration was successful!");
            $form= $this->createForm(UserType::class, new User());
        }

        return $this->render("user/newuser.html.twig", array("form"=> $form->createView(), "page"=>"adduser"));
    }
}
