<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DefaultController
 *
 * @author enesi
 */
class DefaultController extends Controller {

    //put your code here
    /**
     * @Route("/", name="home")
     */
    public function index() {
        return $this->render("/default/index.html.twig", array("page"=>"home"));
    }

}
