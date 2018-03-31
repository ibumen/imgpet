<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\{
    Response,
    Request
};
use App\Form\LoadingStationType;
use App\Entity\{LoadingStation, Loading};

class LoadingStationController extends Controller {

    /**
     * 
     * @Route("/station/list", name="liststation")
     */
    public function listStation() {
        //$this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em = $this->getDoctrine()->getRepository(\App\Entity\LoadingStation::class);
        $stations = $em->findAll();
        return $this->render("station/liststation.html.twig", array("page" => "liststation", "stations" => $stations));
    }

    /**
     * 
     * @Route("/station/add", name="addstation")
     */
    public function addStation(Request $request) {
        // replace this line with your own code!
        //$this->denyAccessUnlessGranted('ROLE_ADMIN');
        $station = new LoadingStation();
        $form = $this->createForm(LoadingStationType::class, $station);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($station);
            $em->flush();
            $this->addFlash("registrationsuccess", "Loading depot registration was successful!");
            $form = $this->createForm(LoadingStationType::class, new LoadingStation());
        }

        return $this->render("station/newstation.html.twig", array("form" => $form->createView(), "page" => "addstation"));
    }

    /**
     * @Route("/station/del/{stationid}", name="del_station", requirements={"stationid":"\d+"})
     */
    public function delStation(Request $request, $stationid) {
        if (/* true || */$request->isXmlHttpRequest()) {
            $doct = $this->getDoctrine();
            $station = $doct->getRepository(LoadingStation::class)->find($stationid);
            if (null === $station) {
                return new Response('error_1');
            }
            $rep = $doct->getRepository(Loading::class);
            $recs = $rep->countLoadingFromStation($station);
            if ($recs) {
                return new Response("error_4");
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
     * @Route("/station/edit/{stationid}", name="editstation", requirements={"stationid":"\d+"})
     */
    public function editStation(Request $request, $stationid) {
        //return;
        //$this->denyAccessUnlessGranted('ROLE_ADMIN');
        // replace this line with your own code!
        $station = $this->getDoctrine()->getRepository(LoadingStation::class)->find($stationid);
        if (null === $station) {
            throw new \InvalidArgumentException("Loading depot does not exist");
        }

        $form = $this->createForm(LoadingStationType::class, $station);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            try {
                $em->persist($station);
                $em->flush();
                $this->addFlash("modificationsuccess", "Loading depot modification was successful!");
            } catch (Exception $e) {
                $form->addError(new \Symfony\Component\Form\FormError("Modification failed. Verify that the name of the loading depot is not in use."));
            }
        }

        return $this->render("station/editstation.html.twig", array("form" => $form->createView(), "page" => "addstation"));
    }

}
