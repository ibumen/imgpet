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
    OrderType
};
use App\Entity\{
    Order,
    Customer,
    Transaction,
    ProductDistribution
};
Use Doctrine\ORM\EntityManagerInterface;

class OrderController extends Controller {

    /**
     * @Route("/order/list/{customerid}/{status}", name="listorder", requirements={"customerid":"\d+","status":"active|completed|cancelled"})
     */
    public function listOrder($customerid = null, $status = null) {
        $this->denyAccessUnlessGranted('ROLE_PERSONNEL_SALES');
        $arrdata = array("page" => "listorder");
        if (isset($customerid)) {
            $em = $this->getDoctrine()->getRepository(\App\Entity\Order::class);
            $orders = (!isset($status) ? ($em->findByCustomer($customerid, array("orderDate" => "DESC"))) : ($em->findAllStatusOrder($status, $customerid, array("order" => "DESC"))));
            if (count($orders)) {
                $customer = $orders[0]->getCustomer();
                $arrdata['customer'] = $customer;
            }
        } else {
            $em = $this->getDoctrine()->getRepository(\App\Entity\Order::class);
            $orders = $em->findBy(array(), array("orderDate" => "DESC"));
        }
        $arrdata['orders'] = $orders;
        return $this->render("order/listorder.html.twig", $arrdata);
    }

    /**
     * @Route("/order/add/{customerid}", name="addorder", requirements={"customerid":"\d+"})
     */
    public function addOrder(Request $request, Swift_Mailer $mailer, $customerid = null) {
        $this->denyAccessUnlessGranted('ROLE_PERSONNEL_SALES');
        // replace this line with your own code!
        $order = new Order();

        $oid = "ODR";
        $order->setDateRecorded(new \DateTime());
        $doct = $this->getDoctrine();
        $lastorder = $doct->getRepository(Order::class)->findOneByDateRecorded();
        if (count($lastorder)) {
            $lastDate = $lastorder[0]->getDateRecorded();
        } else {
            $lastDate = $order->getDateRecorded();
        }

        if (count($lastorder) == 0 || $order->getDateRecorded()->diff($lastDate)->format("%d") > 0) {
            $oid .= $order->getDateRecorded()->format("Ymd") . "001";
        } else {
            $dtstr = $lastDate->format("Ymd");
            $loid = $lastorder[0]->getOid();
            $sn = substr($loid, 11);
            $sn = $sn + 1;
            $oid .= $dtstr . (($sn < 10) ? ("00$sn") : (($sn < 100) ? ("0$sn") : ($sn)));
        }
        $order->setOid($oid);

        if (isset($customerid)) {
            $customer = $doct->getRepository(Customer::class)->find($customerid);
            if ($customer) {
                $order->setCustomer($customer);
            }
        }
        $order->setSalesPerson($this->getUser());
        $form = $this->createForm(OrderType::class, $order);
        //echo $oid; exit();
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doct->getManager();
            $customeremail = $order->getCustomer()->getEmail();

            //echo $order->getDateRecorded()->format("Y-m-d"); exit();
            /*             * ****************** */
            if ($form->get('maketrans')->getData() == true || $order->getCustomer()->getCid() == "C00000000000") {
                $transaction = new Transaction();
                $transaction->setDateRecorded($order->getDateRecorded());
                $tid = "TRXN";
                $lasttrans = $doct->getRepository(Transaction::class)->findOneByDateRecorded();
                if (count($lasttrans)) {
                    $lastDate = $lasttrans[0]->getDateRecorded();
                } else {
                    $lastDate = $transaction->getDateRecorded();
                }
                if (count($lasttrans) == 0 || $transaction->getDateRecorded()->diff($lastDate)->format("%d") > 0) {
                    $tid .= $transaction->getDateRecorded()->format("Ymd") . "001";
                } else {
                    $dtstr = $lastDate->format("Ymd");
                    $ltid = $lasttrans[0]->getTid();
                    $sn = substr($ltid, 12);
                    $sn = $sn + 1;
                    $tid .= $dtstr . (($sn < 10) ? ("00$sn") : (($sn < 100) ? ("0$sn") : ($sn)));
                }
                $transaction->setTid($tid);
                if ($order->getCustomer()->getCid() == "C00000000000") {
                    $transaction->setAmountPaid($order->getAmountDue());
                } else {
                    $transaction->setAmountPaid($form->get("Transaction")->get("amountPaid")->getData());
                }
                $transaction->setPaymentMethod($form->get("Transaction")->get("paymentMethod")->getData());
                $transaction->setOrder($order);
                $transaction->setTransDate($order->getOrderDate());
                $transaction->setCommittedBy($this->getUser());
                if (is_numeric($transaction->getAmountPaid()) && $transaction->getAmountPaid() > 0 && $transaction->getAmountPaid() <= $order->getAmountDue()) {
                    if ($transaction->getAmountPaid() == $order->getAmountDue()) {
                        $order->setClosedBy($this->getUser());
                        $order->setClosingRemark("Completed on last transaction.");
                        $order->setDateClosed(new \DateTime());
                        $order->setOrderStatus("completed");
                    }
                    $em->persist($order);
                    $em->persist($transaction);
                    $em->flush();

                    $order->getCustomer()->addOrder($order);
                    $order->addTransaction($transaction);

                    /* Mailer                    * ****** */
                    if (!empty($customeremail)) {
                        $message1 = (new \Swift_Message('Order Confirmation'))
                                ->setFrom('auto_confirm@imgpet.com')
                                ->setTo($customeremail)
                                ->setBody(
                                $this->renderView(
                                        // templates/emails/registration.html.twig
                                        'email/neworder.html.twig', array('order' => $order)
                                ), 'text/html'
                        );
                        $mailer->send($message1);
                        $message2 = (new \Swift_Message('Payment Notification'))
                                ->setFrom('auto_confirm@imgpet.com')
                                ->setTo($customeremail)
                                ->setBody(
                                $this->renderView(
                                        // templates/emails/registration.html.twig
                                        'email/newtransaction.html.twig', array('trxn' => $transaction)
                                ), 'text/html'
                        );
                        $mailer->send($message2);
                    }

                    return $this->redirectToRoute("vieworder", array('orderid' => $order->getId()));
                } else {
                    $form->get("Transaction")->get("amountPaid")->addError(new \Symfony\Component\Form\FormError("Amount paid must be numeric and not greater than amount payable on order."));
                }
            } else {
                $em->persist($order);
                $em->flush();
                $order->getCustomer()->addOrder($order);
                /* Mailer                    * ****** */
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
                }

                return $this->redirectToRoute("vieworder", array('orderid' => $order->getId()));
            }
            /*             * ***************** */

            //$this->addFlash("registrationsuccess", "Order was placed successfully!");
            //$form = $this->createForm(OrderType::class, new Order());
        }
        return $this->render("order/neworder.html.twig", array("form" => $form->createView(), "page" => "addorder"));
    }

    /**
     * @Route("/order/delete/{orderid}", name="deleteorder", requirements={"orderid":"\d+"})
     */
    public function deleteOrder(EntityManagerInterface $emi, Request $request, $orderid) {
        $this->denyAccessUnlessGranted('ROLE_MANAGER_SALES');
        $order = $emi->getRepository(Order::class)->find($orderid);
        if ($order && null !== $order) {
            $this->addFlash("orderdeletion", "Cannot delete order!");
        }
        $oid = $order->getOid();
        if (count($order->getTransactions())) {
            $this->addFlash("orderdeletion", "Cannot delete order '$oid'!");
        } else if ($order->getOrderDeliveryStatus()!="not-delivered"){
            $this->addFlash("orderdeletion", "Cannot delete order '$oid'!");
        }else {

            $repo = $emi->getRepository(Order::class);
            $result = $repo->deleteOrder($orderid);

            if ($result) {
                $this->addFlash("orderdeletion", "Order '$oid' was deleted successfull!");
            } else {
                $this->addFlash("orderdeletion", "Cannot delete order '$oid'!");
            }
        }

        return $this->redirectToRoute("listorder");
    }

    /**
     * @Route("/order/edit/{orderid}", name="editorder", requirements={"orderid":"\d+"})
     */
    public function editOrder(EntityManagerInterface $emi, Request $request, $orderid) {
        $this->denyAccessUnlessGranted('ROLE_PERSONNEL_SALES');
        $order = $emi->getRepository(Order::class)->find($orderid);
        if (!$order && null === $order) {
            throw new \InvalidArgumentException("Order does not exist!");
        }

        $form = $this->createForm(OrderType::class, $order);

        $form->handleRequest($request);
        $arrdata = array("page" => "editorder");
        if ($form->isSubmitted() && $form->isValid()) {
            if (count($order->getTransactions())) {
                $form->addError(new \Symfony\Component\Form\FormError("One or more transactions have been made on this order"));
            } else if ($order->getOrderDeliveryStatus() != "not-delivered") {
                $form->addError(new \Symfony\Component\Form\FormError("This order might have been delivered to the client"));
            } else {
                $em = $this->getDoctrine()->getManager();
                $em->persist($order);
                $em->flush();
                $this->addFlash("modificationsuccess", "Order modification was successful!");
                $arrdata['orderid'] = $order->getId();
            }
        }

        $arrdata["form"] = $form->createView();

        return $this->render("order/editorder.html.twig", $arrdata);
    }

    /**
     * @Route("/order/view/{orderid}/{customerid}", name="vieworder", requirements={"orderid":"\d+|last","customerid":"\d+"})
     */
    public function viewOrder(Request $request, $orderid, $customerid = null) {
        $this->denyAccessUnlessGranted('ROLE_PERSONNEL_SALES');
        // replace this line with your own code!
        $arrdata = array("page" => "vieworder");

        $em = $this->getDoctrine();
        $rep = $em->getRepository(Order::class);
        if ($orderid == 'last') {
            $order = $rep->findLastOrder($customerid);
        } else {
            $order = $rep->find($orderid);

            if (!$order) {
                $order = $rep->findByOid($orderid);
            }
        }

        if ($order) {
            if (is_array($order)) {
                $order = $order[0];
            }
            $arrdata['order'] = $order;
        }
        $form = $this->createFormBuilder(new \App\Utility\OrderFind())
                ->setAction($this->generateUrl('vorder'))
                ->add('oid', TextType::class, array("label" => "Order ID:", "data" => ($order) ? $order->getOid() : "", "attr" => array("placeholder" => "Order ID")))
                ->add('Load', SubmitType::class, array('label' => 'Load'))
                ->getForm();
        if (!$order) {
            $form->addError(new \Symfony\Component\Form\FormError("Order record not found!"));
        }
        $rep2 = $em->getRepository(ProductDistribution::class);
        $dl = $rep2->getDeliveryLocation($order);
        $arrdata['deliverylocation'] = $dl;
        $arrdata['form'] = $form->createView();



        return $this->render("order/vieworder.html.twig", $arrdata);
    }

    /**
     * @Route("/order/view", name="vorder")
     */
    public function vOrder(Request $request) {
        $this->denyAccessUnlessGranted('ROLE_PERSONNEL_SALES');
        // replace this line with your own code!
        $form = $this->createFormBuilder(new \App\Utility\OrderFind())
                ->setAction($this->generateUrl('vorder'))
                ->add('oid', TextType::class, array("label" => "Order ID:", "attr" => array("placeholder" => "Order ID")))
                ->add('Load', SubmitType::class, array('label' => 'Load'))
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $orderid = $form->get('oid')->getData();
            return $this->forward('App\Controller\OrderController::viewOrder', array("orderid" => $orderid));
        }
        return $this->render("order/vieworder.html.twig", array("form" => $form->createView(), "page" => "vieworder"));
    }

    /* Utilities */
}
