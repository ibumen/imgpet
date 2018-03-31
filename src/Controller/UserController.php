<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\{
    Response,
    Request
};
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Form\UserType;
use App\Entity\User;

class UserController extends Controller {

    /**
     * @Route("/user/list", name="listuser")
     */
    public function listUser() {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em = $this->getDoctrine()->getRepository(\App\Entity\User::class);
        $users = $em->findAll();
        return $this->render("user/listuser.html.twig", array("page" => "listuser", "users" => $users));
    }

    /**
     * @Route("/user/add", name="adduser")
     */
    public function addUser(Request $request, UserPasswordEncoderInterface $passwordEncoder) {
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
            $form = $this->createForm(UserType::class, new User());
        }

        return $this->render("user/newuser.html.twig", array("form" => $form->createView(), "page" => "adduser"));
    }

    /**
     * @Route("/user/status/change/{userid}", name="change_user_status", requirements={"userid":"\d+"})
     */
    public function changeUserStatus(Request $request, $userid) {
        try {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
        } catch (Exception $e) {
            return new Response("error_0");
        }
        if (/* true || */$request->isXmlHttpRequest()) {
            if ($this->getUser()->getId() == $userid) {
                return new Repsone("0");
            }

            $doct = $this->getDoctrine();
            $user = $doct->getRepository(User::class)->find($userid);
            if (null === $user) {
                return new Response('error_1');
            }
            try {
                $user->setStatus(($user->getStatus() == "enabled") ? ("disabled") : ("enabled"));
                $mgr = $doct->getManager();
                $mgr->persist($user);
                $mgr->flush();
            } catch (Exception $e) {
                return new Response("error_3");
            }
            return new Response('0');
        } else {
            return new Response("error_0");
        }
    }

    /**
     * @Route("/user/password/reset/{userid}", name="reset_user_password", requirements={"userid":"\d+"})
     */
    public function resetUserPassword(Request $request, UserPasswordEncoderInterface $encoder, $userid) {
        try {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
        } catch (Exception $e) {
            return new Response("error_0");
        }

        if (/* true || */$request->isXmlHttpRequest() || $request->getMethod() == 'POST') {

            $doct = $this->getDoctrine();
            $user = $doct->getRepository(User::class)->find($userid);
            if (null === $user) {
                return new Response('error_1');
            }
            $user->setPlainPassword("password");
            $password = $encoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            try {
                $mgr = $doct->getManager();
                $mgr->persist($user);
                $mgr->flush();
            } catch (Exception $e) {
                return new Response("error_3");
            }
            return new Response('0');
        } else {
            return new Response("error_0");
        }
    }

    /**
     * @Route("/user/del/{userid}", name="del_user", requirements={"userid":"\d+"})
     */
    public function delUser(Request $request, $userid) {
        try {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
        } catch (Exception $e) {
            return new Response("error_0");
        }

        if (/* true || */$request->isXmlHttpRequest()) {
            if ($this->getUser()->getId() == $userid) {
                return new Response("0");
            }

            $doct = $this->getDoctrine();
            $rep = $doct->getRepository(User::class);
            $user = $rep->find($userid);
            if (null === $user) {
                return new Response('error_1');
            }
            if ($rep->hasRecord($user)) {
                return new Response("error_4");
            }

            try {
                $mgr = $doct->getManager();
                $mgr->remove($user);
                $mgr->flush();
            } catch (Exception $e) {
                return new Response("error_3");
            }
            return new Response('0');
        }
    }

}
