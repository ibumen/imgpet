<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\{Response, Request};

use App\Form\TruckOwnerType;
use App\Entity\TruckOwner;

class TruckOwnerController extends Controller
{
    /**
     * @Route("/truckowner/list", name="listtruckowner")
     */
    public function listTruckOwner()
    {
        $this->denyAccessUnlessGranted('ROLE_ASSET_MANAGER');
        $em= $this->getDoctrine()->getRepository(\App\Entity\TruckOwner::class);
        $truckowners = $em->findAll();
        return $this->render("truckowner/listtruckowner.html.twig", array("page"=>"listtruckowner", "truckowners"=>$truckowners));
    }
    
    /**
     * @Route("/truckowner/add", name="addtruckowner")
     */
    public function addTruckowner(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ASSET_MANAGER');
        // replace this line with your own code!
        $truckowner = new TruckOwner();
        $form = $this->createForm(TruckOwnerType::class, $truckowner);
        
        $form->handleRequest($request);
        
         if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($truckowner);
            $em->flush();
            $this->addFlash("registrationsuccess", "Truck Owner registration was successful!");
            $form= $this->createForm(TruckOwnerType::class, new TruckOwner());
        }

        return $this->render("truckowner/newtruckowner.html.twig", array("form"=> $form->createView(), "page"=>"addtruckowner"));
    }
}
