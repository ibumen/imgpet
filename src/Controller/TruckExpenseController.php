<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Swift_Mailer;
use Symfony\Component\HttpFoundation\{
    Response,
    Request
};
use Symfony\Component\Form\Extension\Core\Type\{
    TextType,
    SubmitType
};
use App\Form\TruckExpenseType;
use App\Entity\{
    Truck,
    TruckExpense
};
Use Doctrine\ORM\EntityManagerInterface;

class TruckExpenseController extends Controller {

    use \App\Utility\Utils;

    /**
     * @Route("/truckexpense/list/{truckid}", name="listtruckexpense", requirements={"truckid":"\d+"})
     */
    public function listTruckExpenses($truckid = null) {
        //$this->denyAccessUnlessGranted('ROLE_PERSONNEL_SALES');
        $arrdata = array("page" => "listtruckexpense");
        $em = $this->getDoctrine()->getRepository(\App\Entity\TruckExpense::class);
        if (isset($truckid)) {
            $expenses = $em->findByTruck($truckid);
            if (count($expenses)) {
                $truck = $expenses[0]->getTruck();
                $arrdata['truck'] = $truck;
            }
        } else {
            $expenses = $em->findAll();
        }
        $arrdata['expenses'] = $expenses;
        return $this->render("truckexpense/listtruckexpense.html.twig", $arrdata);
    }

    /**
     * @Route("/truckexpense/add/{truckid}", name="addtruckexpense", requirements={"truckid":"\d+"})
     */
    public function addTruckExpense(Request $request, Swift_Mailer $mailer, $truckid = null) {
        //$this->denyAccessUnlessGranted('ROLE_PERSONNEL_SALES');
        // replace this line with your own code!
        $truckexpense = new TruckExpense();

        $doct = $this->getDoctrine();
        $truckexpense->setEnteredBy($this->getUser());

        $form = $this->createForm(TruckExpenseType::class, $truckexpense, array());
        //echo $tid; exit();
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            $em = $doct->getManager();
            $em->persist($truckexpense);
            $em->flush();

            //$this->addFlash("registrationsuccess", "Truck expense was added!");
            //$form = $this->createForm(TransactionType::class, new Transaction());
            return $this->redirectToRoute("viewtruckexpense", array('truckexpenseid' => $truckexpense->getId()));
        }

        return $this->render("truckexpense/newtruckexpense.html.twig", array("form" => $form->createView(), "page" => "addtruckexpense"));
    }

    /**
     * @Route("/truckexpense/edit/{truckexpenseid}", name="edittruckexpense", requirements={"truckexpenseid":"\d+"})
     */
    public function editTruckExpense(Request $request, $truckexpenseid = null) {
        //$this->denyAccessUnlessGranted('ROLE_PERSONNEL_SALES');
        // replace this line with your own code!

        $doct = $this->getDoctrine();
        $rep = $doct->getRepository(TruckExpense::class);

        $truckexpense = $rep->find($truckexpenseid);

        $form = $this->createForm(TruckExpenseType::class, $truckexpense, array());
        //echo $tid; exit();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $doct->getManager();
            $em->persist($truckexpense);
            $em->flush();

            //$this->addFlash("modificationsuccess", "Truck expense was modified!");
            //$form = $this->createForm(TransactionType::class, new Transaction());
            return $this->redirectToRoute("viewtruckexpense", array('truckexpenseid' => $truckexpense->getId()));
        }

        return $this->render("truckexpense/edittruckexpense.html.twig", array("form" => $form->createView(), "page" => "listtruckexpense"));
    }

    /**
     * @Route("/truckexpense/view/{truckexpenseid}", name="viewtruckexpense", requirements={"truckexpenseid":"\d+"})
     */
    public function viewTruckExpense(Request $request, $truckexpenseid) {
        //$this->denyAccessUnlessGranted('ROLE_PERSONNEL_SALES');
        // replace this line with your own code!
        $arrdata = array("page" => "viewtruckexpense");

        $em = $this->getDoctrine();
        $rep = $em->getRepository(TruckExpense::class);
        $truckexpense = $rep->find($truckexpenseid);
        if (!$truckexpense) {
            return $this->redirectToRoute("listtruckexpense");
        }

        if (is_array($truckexpense)) {
            $truckexpense = $truckexpense[0];
        }
        $arrdata['expense'] = $truckexpense;

        return $this->render("truckexpense/viewtruckexpense.html.twig", $arrdata);
    }

    /**
     * @Route("/truckexpense/del/{truckexpenseid}", name="del_truck_expense", requirements={"truckexpenseid":"\d+"})
     */
    public function delTruckExpense(Request $request, $truckexpenseid) {
        if (/* true || */$request->isXmlHttpRequest()) {
            $doct = $this->getDoctrine();
            $truckexpense = $doct->getRepository(TruckExpense::class)->find($truckexpenseid);
            if (null === $truckexpense) {
                return new Response('error_1');
            }
            try {
                $mgr = $doct->getManager();
                $mgr->remove($truckexpense);
                $mgr->flush();
            } catch (Exception $e) {
                return new Response("error_3");
            }
            return new Response('0');
        }
    }

    /* Utilities */
}
