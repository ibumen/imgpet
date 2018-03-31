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
use App\Form\{
    LoadingType,
    LoadingRecordType
};
use App\Entity\{
    Product,
    Loading,
    LoadingRecord
};
Use Doctrine\ORM\EntityManagerInterface;

class LoadingRecordController extends Controller {

    /**
     * @Route("/loadingrecord/list/{loadingid}", name="listloadingrecord", requirements={"loadingid":"\d+"})
     */
    public function listLoadingRecord($loadingid = null) {
        //$this->denyAccessUnlessGranted('ROLE_SALES_PERSONNEL');
        $arrdata = array("page" => "viewloading");

        $em = $this->getDoctrine()->getRepository(\App\Entity\LoadingRecord::class);
        if (isset($loadingid)) {
            $arrdata['loading'] = $this->getDoctrine()->getRepository(\App\Entity\Loading::class)->find($loadingid);
            $loadings = $em->findByLoading($loadingid);
            if (count($loadings)) {
                $loading = $loadings[0]->getLoading();
                $arrdata['loading'] = $loading;
            }
        } else {
            $loadings = $em->findByDateOfLoading("DESC");
        }
        $arrdata['loadings'] = $loadings;
        return $this->render("loadingrecord/listloadingrecord.html.twig", $arrdata);
    }

    /**
     * @Route("/loadingrecord/add/{loadingid}", name="addloadingrecord", requirements={"loadingid":"\d+"})
     */
    public function addLoadingRecord(Request $request, Swift_Mailer $mailer, $loadingid = null) {
        //$this->denyAccessUnlessGranted('ROLE_SALES_PERSONNEL');
        // replace this line with your own code!
        $viewoptions = array("page" => "viewloading");
        $loading = new LoadingRecord();

        $lrid = "LRD";
        $loading->setDateRecorded(new \DateTime());
        $doct = $this->getDoctrine();
        $lastlrd = $doct->getRepository(LoadingRecord::class)->findOneByDateRecorded();
        if (count($lastlrd)) {
            $lastDate = $lastlrd[0]->getDateRecorded();
        } else {
            $lastDate = $loading->getDateRecorded();
        }
//echo $loading->getDateRecorded()->diff($lastDate)->format("%d");exit();
        if (count($lastlrd) == 0 || $loading->getDateRecorded()->diff($lastDate)->format("%d") > 0) {
            $lrid .= $loading->getDateRecorded()->format("Ymd") . "001";
        } else {
            $dtstr = $lastDate->format("Ymd");
            $llrid = $lastlrd[0]->getLrid();
            $sn = substr($llrid, 11);
            $sn = $sn + 1;
            $lrid .= $dtstr . (($sn < 10) ? ("00$sn") : (($sn < 100) ? ("0$sn") : ($sn)));
        }
        $loading->setLrid($lrid);
        if (isset($loadingid)) {
            $loadingobj = $doct->getRepository(Loading::class)->find($loadingid);
            if ($loadingobj) {
                $options = array("byloading" => true);
                $options['maxquantity'] = $doct->getRepository(Loading::class)->getQuantityUnallocated($loadingobj);
                if ($options['maxquantity'] == 0) {
                    return $this->redirect($request->get("backurl", $this->generateUrl("viewloading", array("loadingid" => $loadingobj->getId()))));
                }
                $viewoptions["loadingdate"] = $loadingobj->getLoadingDate();
                $viewoptions['loading'] = $loadingobj;
                $loading->setLoading($loadingobj);
            }
        }
        $loading->setCreatedBy($this->getUser());
        $form = $this->createForm(LoadingRecordType::class, $loading, $options);
        //echo $lrid; exit();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $haserror = false;
            $rep = $doct->getRepository(LoadingRecord::class);
            if (!$rep->isUniqueTruckNo($loading->getTruckNo())) {
                $haserror = true;
                $form->addError(new \Symfony\Component\Form\FormError("There exist an entry with the truck no: " . $loading->getTruckNo()));
            }

            if (!$rep->isUniqueMeterTicket($loading->getMeterTicket())) {
                $haserror = true;
                $form->addError(new \Symfony\Component\Form\FormError("There exist an entry with the meter ticket: " . $loading->getMeterTicket()));
            }

            if (!$rep->isConsistentQuantity($loading->getQuantity(), $loading->getLoading()->getQuantity(), $loading->getLoading())) {
                $haserror = true;
                $form->addError(new \Symfony\Component\Form\FormError("Quantity supplied will lead to inconsistency in the total quantity loaded or with the quantity delivered."));
            }

            if ((null !== $loading->getDeliveryDate()) && $loading->getLoading()->getLoadingDate() > $loading->getDeliveryDate()) {
                $haserror = true;
                $form->addError(new \Symfony\Component\Form\FormError("Inconsistent date for loading and delivery!"));
            }
            if (!$haserror) {


                $em = $doct->getManager();

                $em->persist($loading);
                $em->flush();

                /* Mailer                    * ****** 
                  if (!empty($customeremail)) {
                  $message = (new \Swift_Message('Order Confirmation'))
                  ->setFrom('auto_confirm@imgpet.com')
                  ->setTo($customeremail)
                  ->setBody(
                  $this->renderView(
                  // templates/emails/registration.html.twig
                  'email/neworder.html.twig', array('order' => $order)
                  ), 'text/html'
                  );
                  $mailer->send($message);
                  } */

                return $this->redirectToRoute("viewloadingrecord", array('loadingid' => $loading->getId()));
            }
        }
        /*         * ***************** */
        $viewoptions["form"] = $form->createView();
        return $this->render("loadingrecord/newloadingrecord.html.twig", $viewoptions);
    }

    /**
     * @Route("/loadingrecord/delete/{loadingid}", name="deleteloadingrecord", requirements={"loadingid":"\d+"})
     */
    public function deleteLoadingRecord(EntityManagerInterface $emi, Request $request, $loadingid) {
        //$this->denyAccessUnlessGranted('ROLE_SALES_MANAGER');
        $loading = $emi->getRepository(LoadingRecord::class)->find($loadingid);
        if ($loading && null !== $loading) {
            $this->addFlash("loadingrecorddeletion", "Cannot delete loading record!");
        }
        $lrid = $loading->getLrid();
        $loadingobj = $loading->getLoading();
        if (in_array($loading->getLoadingStatus(), array("delivered", "delivered with dispute"))) {
            $this->addFlash("loadingdeletion", "Cannot delete loading record '$lrid'!");
        } else {

            $repo = $emi->getRepository(LoadingRecord::class);
            $result = $repo->deleteLoadingRecord($loadingid);

            if ($result) {
                $this->addFlash("loadingdeletion", "Loading recored '$lrid' was deleted successfully!");
            } else {
                $this->addFlash("loadingdeletion", "Cannot delete loading record '$lrid'!");
            }
        }

        return $this->redirectToRoute("listloadingrecord", array("loadingid" => $loadingobj->getId()));
    }

    /**
     * @Route("/loadingrecord/edit/{loadingid}", name="editloadingrecord", requirements={"loadingid":"\d+"})
     */
    public function editLoadingRecord(EntityManagerInterface $emi, Request $request, $loadingid) {
        //$this->denyAccessUnlessGranted('ROLE_SALES_PERSONNEL');
        $loading = $emi->getRepository(LoadingRecord::class)->find($loadingid);
        if (!$loading && null === $loading) {
            throw new \InvalidArgumentException("Loading record does not exist!");
        }
        $options = array('byloading' => true);
//        if (($loading->getQuantity() > $loading->getDeliveredQuantity() || $loading->getCostOfLogistics() != $loading->getLogisticsPaid()) && $loading->getLoadingStatus() != "delivered") {
//            if ($loading->getDeliveredQuantity() == 0) {
//                $options["closingremark"] = "hide";
//            } else {
//                $options["closingremark"] = "show";
//            }
//        } else {
//            $options["closingremark"] = "none";
//        }
        $form = $this->createForm(LoadingRecordType::class, $loading, $options);
        $form->handleRequest($request);
        $arrdata = array("page" => "viewloading");
        if ($form->isSubmitted() && $form->isValid()) {
            $haserror = false;
            $em = $this->getDoctrine()->getManager();
            $rep = $em->getRepository(LoadingRecord::class);
            if (!$rep->isUniqueTruckNo($loading->getTruckNo(), $loading->getId())) {
                $haserror = true;
                $form->addError(new \Symfony\Component\Form\FormError("There exist an entry with the truck no: " . $loading->getTruckNo()));
            }

            if (!$rep->isUniqueMeterTicket($loading->getMeterTicket(), $loading->getId())) {
                $haserror = true;
                $form->addError(new \Symfony\Component\Form\FormError("There exist an entry with the meter ticket: " . $loading->getMeterTicket()));
            }

            if (!$rep->isConsistentQuantity($loading->getQuantity(), $loading->getLoading()->getQuantity(), $loading->getLoading(), $loading->getId())) {
                $haserror = true;
                $form->addError(new \Symfony\Component\Form\FormError("Quantity supplied will lead to inconsistency in the total quantity loaded or with the quantity delivered."));
            }

            if (in_array($loading->getLoadingStatus(), array("delivered", "delivered with dispute"))) {
                $haserror = true;
                $form->addError(new \Symfony\Component\Form\FormError("This loading has been delivered."));
            }
            if (!$haserror) {
                if (($loading->getQuantity() > $loading->getDeliveredQuantity() || $loading->getCostOfLogistics() != $loading->getLogisticsPaid()) && $loading->getLoadingStatus() != "delivered") {
                    if ($loading->getDeliveredQuantity() == 0)
                        $loading->setLoadingStatus("loading");
                    else
                        $loading->setLoadingStatus("delivered with dispute");
                }else {
                    $loading->setLoadingStatus("delivered");
                }
                $em = $this->getDoctrine()->getManager();
                $em->persist($loading);
                $em->flush();
                $this->addFlash("modificationsuccess", "Loaded truck record was modified successfully!");
                $arrdata['loadingid'] = $loading->getId();
            }
        }

        $arrdata["form"] = $form->createView();

        return $this->render("loadingrecord/editloadingrecord.html.twig", $arrdata);
    }

    /**
     * @Route("/loadingrecord/undo/delivery/{loadingrecordid}", name="undo_product_delivery", requirements={"loadingrecordid":"\d+"})
     */
    public function undoDeliverLoadingRecord(EntityManagerInterface $emi, Request $request, $loadingrecordid) {
        $loading = $emi->getRepository(LoadingRecord::class)->find($loadingrecordid);
        if (!$loading && null === $loading) {
            return new Response("error_1");
        }
        if($emi->getRepository(LoadingRecord::class)->undoDelivery($loadingrecordid)){
            return new Response("0");
        }
        return new Response("error_3");
    }

    /**
     * @Route("/loadingrecord/delivery/{loadingid}", name="deliverloadingrecord", requirements={"loadingid":"\d+"})
     */
    public function deliverLoadingRecord(EntityManagerInterface $emi, Request $request, $loadingid) {
        //$this->denyAccessUnlessGranted('ROLE_SALES_PERSONNEL');
        $loading = $emi->getRepository(LoadingRecord::class)->find($loadingid);
        if (!$loading && null === $loading) {
            throw new \InvalidArgumentException("Loading record does not exist!");
        }

        $options = array('byloading' => true);
        $options['delivery'] = true;
        $options['maxquantity'] = $loading->getQuantity();
        $options['loadingdate'] = $loading->getLoading()->getLoadingDate();
        $options['costoflogistics'] = $loading->getCostOfLogistics();
        $options['logisticspaid'] = $loading->getLogisticsPaid();
        $form = $this->createForm(LoadingRecordType::class, $loading, $options);
        $arrdata = array("page" => "viewloading");
        $arrdata['loading'] = clone $loading;
        $form->handleRequest($request);
        $haserror = false;

        //$arrdata['loading'] = $loading;
        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            //$rep = $em->getRepository(LoadingRecord::class);
            if ($loading->getDeliveredQuantity() == 0 || $loading->getDeliveredQuantity() < 0) {
                $haserror = true;
                $form->get("deliveredQuantity")->addError(new \Symfony\Component\Form\FormError("Provide quantity of product delivered."));
            }

            if (null === $loading->getDeliveryDate() || $loading->getDeliveryDate() == "") {
                $haserror = true;
                $form->get("deliveryDate")->addError(new \Symfony\Component\Form\FormError("Provide delivery date."));
            } else {
                if ($loading->getLoading()->getLoadingDate()->diff($loading->getDeliveryDate())->format('%rs') > 0) {
                    //echo $loading->getLoading()->getLoadingDate()->diff($loading->getDeliveryDate())->format('%rs'); exit;
                    $haserror = true;
                    $form->get("deliveryDate")->addError(new \Symfony\Component\Form\FormError("Delivery date cannot be earlier than date of loading."));
                }
            }

            if (!$haserror) {
                if (($loading->getQuantity() > $loading->getDeliveredQuantity() || $loading->getCostOfLogistics() != $loading->getLogisticsPaid()) && $loading->getLoadingStatus() != "delivered") {
                    if ($loading->getDeliveredQuantity() == 0)
                        $loading->setLoadingStatus("loading");
                    else
                        $loading->setLoadingStatus("delivered with dispute");
                }else {
                    $loading->setLoadingStatus("delivered");
                }
                $loading->setFinishedBy($this->getUser());
                $em = $this->getDoctrine()->getManager();
                $em->persist($loading);
                $em->flush();
                $this->addFlash("modificationsuccess", "Delivery details submitted successfully!");
                $arrdata['loadingid'] = $loading->getId();
            }
        }

        $arrdata["form"] = $form->createView();
        $arrdata['haserror'] = $haserror;

        return $this->render("loadingrecord/deliverloadingrecord.html.twig", $arrdata);
    }

    /**
     * @Route("/loadingrecord/view/{loadingid}", name="viewloadingrecord", requirements={"loadingid":"\d+"})
     */
    public function viewLoadingRecord(Request $request, $loadingid) {
        //$this->denyAccessUnlessGranted('ROLE_SALES_PERSONNEL');
        // replace this line with your own code!
        $arrdata = array("page" => "viewloading");

        $em = $this->getDoctrine();
        $rep = $em->getRepository(LoadingRecord::class);

        $loading = $rep->find($loadingid);

        if (!$loading) {
            $loading = $rep->findByLrid($loadingid);
        }

        if ($loading) {
            if (is_array($loading)) {
                $loading = $loading[0];
            }
            $arrdata['loading'] = $loading;
        }
        $form = $this->createFormBuilder(new \App\Utility\LoadingRecordFind())
                ->setAction($this->generateUrl('vloadingrecord'))
                ->add('lrid', TextType::class, array("label" => "Loading Record ID:", "data" => ($loading) ? $loading->getLrid() : "", "attr" => array("placeholder" => "Loading Record ID")))
                ->add('Load', SubmitType::class, array('label' => 'Load'))
                ->getForm();
        if (!$loading) {
            $form->addError(new \Symfony\Component\Form\FormError("Loading record not found!"));
        }

        $arrdata['form'] = $form->createView();



        return $this->render("loadingrecord/viewloadingrecord.html.twig", $arrdata);
    }

    /**
     * @Route("/loadingrecord/viewnotexisting", name="vloadingrecord")
     */
    public function vLoadingRecord(Request $request) {
        //$this->denyAccessUnlessGranted('ROLE_SALES_PERSONNEL');
        // replace this line with your own code!
        $form = $this->createFormBuilder(new \App\Utility\LoadingRecordFind())
                ->setAction($this->generateUrl('vloadingrecord'))
                ->add('lrid', TextType::class, array("label" => "Loading Record ID:", "attr" => array("placeholder" => "Loading Record ID")))
                ->add('Load', SubmitType::class, array('label' => 'Load'))
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $loadingid = $form->get('lrid')->getData();
            return $this->forward('App\Controller\LoadingRecordController::viewLoadingRecord', array("loadingid" => $loadingid));
        }
        return $this->render("loadingrecord/viewloadingrecord.html.twig", array("form" => $form->createView(), "page" => "viewloadingrecord"));
    }

    /* Utilities */
}
