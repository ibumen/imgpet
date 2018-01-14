<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\{Response, Request};

use App\Form\TruckType;
use App\Entity\Truck;

class TruckController extends Controller
{
    /**
     * @Route("/truck/list", name="listtruck")
     */
    public function listTruck()
    {
        $this->denyAccessUnlessGranted('ROLE_ASSET_MANAGER');
        $em= $this->getDoctrine()->getRepository(\App\Entity\Truck::class);
        $trucks = $em->findAll();
        return $this->render("truck/listtruck.html.twig", array("page"=>"listtruck", "trucks"=>$trucks));
    }
    
    /**
     * @Route("/truck/list/{owner}", name="listtruckbyowner", requirements={"owner": "\d+"})
     */
    public function listTruckByOwner($owner)
    {
        $this->denyAccessUnlessGranted('ROLE_ASSET_MANAGER');
        $em= $this->getDoctrine()->getRepository(\App\Entity\TruckOwner::class);
        $ownerobj = $em->find($owner); 
        $trucks =  $ownerobj->getTrucks();
        return $this->render("truck/listtruck.html.twig", array("page"=>"listtruck", "trucks"=>$trucks, "owner"=>$ownerobj));
    }
    
    /**
     * @Route("/truck/add", name="addtruck")
     */
    public function addTruck(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ASSET_MANAGER');
        // replace this line with your own code!
        $truck = new Truck();
        $form = $this->createForm(TruckType::class, $truck);
        
        $form->handleRequest($request);
        
         if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($truck);
            $em->flush();
            $this->addFlash("registrationsuccess", "Truck registration was successful!");
            $form= $this->createForm(TruckType::class, new Truck());
        }

        return $this->render("truck/newtruck.html.twig", array("form"=> $form->createView(), "page"=>"addtruck"));
    }
    /**
     * @Route("/truck/add/{owner}", name="addtrucktoowner", requirements={"owner":"\d+"})
     */
    public function addTruckToOwner(Request $request, $owner)
    {
        $this->denyAccessUnlessGranted('ROLE_ASSET_MANAGER');
        // replace this line with your own code!
        $truck = new Truck();
        $ownerobj = $em = $this->getDoctrine()->getRepository(\App\Entity\TruckOwner::class)->find($owner);
        $truck->setTruckOwner($ownerobj);
        $form = $this->createForm(TruckType::class, $truck);
        
        $form->handleRequest($request);
        
         if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($truck);
            $em->flush();
            $this->addFlash("registrationsuccess", "Truck registration was successful!");
            $form= $this->createForm(TruckType::class, new Truck());
        }

        return $this->render("truck/newtruck.html.twig", array("form"=> $form->createView(), "page"=>"addtruck"));
    }
}
