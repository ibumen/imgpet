<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\{
    Response,
    Request
};
use App\Form\FuelStationType;
use App\Entity\FuelStation;

class FuelStationController extends Controller {

    /**
     * 
     * @Route("/fuelstation/list", name="listfuelstation")
     */
    public function listStation() {
        //$this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em = $this->getDoctrine()->getRepository(\App\Entity\FuelStation::class);
        $stations = $em->findAll();
        return $this->render("fuelstation/listfuelstation.html.twig", array("page" => "listfuelstation", "stations" => $stations));
    }

    /**
     * 
     * @Route("/fuelstation/add", name="addfuelstation")
     */
    public function addStation(Request $request) {
        // replace this line with your own code!
        //$this->denyAccessUnlessGranted('ROLE_ADMIN');
        $station = new FuelStation();
        $form = $this->createForm(FuelStationType::class, $station);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($station);
            $em->flush();
            $this->addFlash("registrationsuccess", "Fuel station added was successful!");
            $form = $this->createForm(FuelStationType::class, new FuelStation());
        }

        return $this->render("fuelstation/newfuelstation.html.twig", array("form" => $form->createView(), "page" => "addfuelstation"));
    }

    /**
     * 
     * @Route("/fuelstation/del/{stationid}", name="del_fuel_station", requirements={"stationid":"\d+"})
     */
    public function delStation(Request $request, $stationid) {
        if (/* true || */$request->isXmlHttpRequest()) {
            $doct = $this->getDoctrine();
            $station = $doct->getRepository(FuelStation::class)->find($stationid);
            if (null === $station) {
                return new Response('error_1');
            }
            try {
                $mgr = $doct->getManager();
                $mgr->remove($station);
                $mgr->flush();
            } catch (Exception $e) {
                return new Response("error_3");
            }
            return new Response('0');
        }
    }

    /**
     * 
     * @Route("/product/edit/{stationid}", name="editfuelstation", requirements={"stationid":"\d+"})
     */
    public function editStation(Request $request, $stationid) {
        return;
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        // replace this line with your own code!
        $product = new LoadingStation();
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();
            $this->addFlash("registrationsuccess", "Product registration was successful!");
            $form = $this->createForm(ProductType::class, new LoadingStation());
        }

        return $this->render("product/newproduct.html.twig", array("form" => $form->createView(), "page" => "addproduct"));
    }

}
