<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\{
    Response,
    Request
};
use App\Form\TruckType;
use App\Entity\Truck;

class TruckController extends Controller {

    /**
     * @Route("/truck/list", name="listtruck")
     */
    public function listTruck() {
        $this->denyAccessUnlessGranted('ROLE_MANAGER_FACILITY');
        $em = $this->getDoctrine()->getRepository(\App\Entity\Truck::class);
        $trucks = $em->findAll();
        return $this->render("truck/listtruck.html.twig", array("page" => "listtruck", "trucks" => $trucks));
    }

    /**
     * @Route("/truck/list/{owner}", name="listtruckbyowner", requirements={"owner": "\d+"})
     *
      public function listTruckByOwner($owner) {
      $this->denyAccessUnlessGranted('ROLE_ASSET_MANAGER');
      $em = $this->getDoctrine()->getRepository(\App\Entity\TruckOwner::class);
      $ownerobj = $em->find($owner);
      $trucks = $ownerobj->getTrucks();
      return $this->render("truck/listtruck.html.twig", array("page" => "listtruck", "trucks" => $trucks, "owner" => $ownerobj));
      } */

    /**
     * @Route("/truck/add", name="addtruck")
     */
    public function addTruck(Request $request) {
        $this->denyAccessUnlessGranted('ROLE_MANAGER_FACILITY');
        // replace this line with your own code!
        $truck = new Truck();
        $form = $this->createForm(TruckType::class, $truck);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($truck);
            $em->flush();
            $this->addFlash("registrationsuccess", "Truck registration was successful!");
            $form = $this->createForm(TruckType::class, new Truck());
        }

        return $this->render("truck/newtruck.html.twig", array("form" => $form->createView(), "page" => "addtruck"));
    }

    /**
     * @Route("/truck/add/{owner}", name="addtrucktoowner", requirements={"owner":"\d+"})
     *
      public function addTruckToOwner(Request $request, $owner) {
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
      $form = $this->createForm(TruckType::class, new Truck());
      }

      return $this->render("truck/newtruck.html.twig", array("form" => $form->createView(), "page" => "addtruck"));
      } */

    /**
     * @Route("/truck/status/change/{truckid}", name="change_truck_status", requirements={"truckid":"\d+"})
     */
    public function changeTruckStatus(Request $request, $truckid) {
        if (/* true || */$request->isXmlHttpRequest()) {
            $doct = $this->getDoctrine();
            $truck = $doct->getRepository(Truck::class)->find($truckid);
            if (null === $truck) {
                return new Response('error_1');
            }
            try {
                $truck->setTruckStatus(($truck->getTruckStatus() == "active") ? ("inactive") : ("active"));
                $mgr = $doct->getManager();
                $mgr->persist($truck);
                $mgr->flush();
            } catch (Exception $e) {
                return new Response("error_3");
            }
            return new Response('0');
        }
    }

    /**
     * @Route("/truck/del/{truckid}", name="del_truck", requirements={"truckid":"\d+"})
     */
    public function delTruck(Request $request, $truckid) {
        if (/* true || */$request->isXmlHttpRequest()) {
            $doct = $this->getDoctrine();
            $rep = $doct->getRepository(Truck::class);
            $truck = $rep->find($truckid);
            if (null === $truck) {
                return new Response('error_1');
            } else
                if ($rep->hasRecord($truck)) {
                    return new Response('error_4');                
            } else {
                try {
                    $mgr = $doct->getManager();
                    $mgr->remove($truck);
                    $mgr->flush();
                } catch (Exception $e) {
                    return new Response("error_3");
                }
                return new Response('0');
            }
        }
    }

}
