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
use App\Form\TransactionType;
use App\Entity\{
    Order,
    Customer,
    Transaction
};
Use Doctrine\ORM\EntityManagerInterface;

class TransactionController extends Controller {

    use \App\Utility\Utils;

    /**
     * @Route("/transaction/list/{orderid}", name="listtransaction", requirements={"orderid":"\d+"})
     */
    public function listTransaction($orderid = null) {
        $this->denyAccessUnlessGranted('ROLE_SALES_PERSONNEL');
        $arrdata = array("page" => "listtransaction");
        $em = $this->getDoctrine()->getRepository(\App\Entity\Transaction::class);
        if (isset($orderid)) {
            $transactions = $em->findByOrder($orderid);
            if (count($transactions)) {
                $order = $transactions[0]->getOrder();
                $arrdata['order'] = $order;
            }
        } else {
            $transactions = $em->findAll();
        }
        $arrdata['transactions'] = $transactions;
        $paymodes = $this->getUtilPaymentOptions();
        sort($paymodes);
        $arrdata['paymodes'] = $paymodes;
        return $this->render("transaction/listtransaction.html.twig", $arrdata);
    }

    /**
     * @Route("/transaction/add/{orderid}", name="addtransaction", requirements={"orderid":"\d+"})
     */
    public function addTransaction(Request $request, Swift_Mailer $mailer, $orderid = null) {
        $this->denyAccessUnlessGranted('ROLE_SALES_PERSONNEL');
        // replace this line with your own code!
        $transaction = new Transaction();

        $tid = "TRXN";
        $doct = $this->getDoctrine();
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

        if (isset($orderid)) {
            $order = $doct->getRepository(Order::class)->find($orderid);
            if ($order) {
                $transaction->setOrder($order);
                $transaction->setAmountPaid($order->getAmountDue());
            }
        }
        $transaction->setCommittedBy($this->getUser());
        $cuid = ($request->get('cuid') == "") ? null : $request->get('cuid');


        $form = $this->createForm(TransactionType::class, $transaction, array("customerid" => $cuid, "onorder" => false));
        //echo $tid; exit();
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $ferr = false;
            if ($transaction->getOrder()->getOrderStatus() !== "active" || $transaction->getOrder()->getAmountDue() == 0) {
                $ferr = true;
                $form->get("amountPaid")->addError(new \Symfony\Component\Form\FormError("Transaction cannot be made on " . $transaction->getOrder()->getOid() . " because it is no longer active."));
            }
            if ($transaction->getTransDate() < $transaction->getOrder()->getOrderDate()) {
                $ferr = true;
                $form->get("transDate")->addError(new \Symfony\Component\Form\FormError("Transaction date must not be a date before the Order (" . $transaction->getOrder()->getOid() . ")."));
            }
            if ($transaction->getAmountPaid() > $transaction->getOrder()->getAmountDue()) {
                $ferr = true;
                $form->get("amountPaid")->addError(new \Symfony\Component\Form\FormError("Transaction amount cannot be greater than amount due (" . number_format($transaction->getOrder()->getAmountDue(), 2) . ") of order (" . $transaction->getOrder()->getOid() . ")."));
            }
            if ($ferr) {
                return $this->render("transaction/newtransaction.html.twig", array("form" => $form->createView(), "page" => "addtransaction"));
            }
            $amt2pay = $transaction->getOrder()->getAmountDue();
            $em = $doct->getManager();
            $em->persist($transaction);
            if ($amt2pay == $transaction->getAmountPaid()) {
                $transaction->getOrder()->setOrderStatus('completed');
                $transaction->getOrder()->setClosedBy($this->getUser());
                $transaction->getOrder()->setDateClosed(new \DateTime());
                $transaction->getOrder()->setClosingRemark('Completed on last transaction.');
            }
            $em->flush();
            $transaction->getOrder()->addTransaction($transaction);
            /* Mailer ************************ */
            $customeremail = $transaction->getOrder()->getCustomer()->getEmail();
            if (!empty($customeremail)) {
                $message = (new \Swift_Message('Payment Notification'))
                        ->setFrom('contactenesi@gmail.com')
                        ->setTo($customeremail)
                        ->setBody(
                        $this->renderView(
                                // templates/emails/registration.html.twig
                                'email/newtransaction.html.twig', array('trxn' => $transaction)
                        ), 'text/html'
                );
                $mailer->send($message);
            }

            //$this->addFlash("registrationsuccess", "Transaction was made successfully!");
            //$form = $this->createForm(TransactionType::class, new Transaction());
            return $this->redirectToRoute("viewtransaction", array('transid' => $transaction->getId()));
        }

        return $this->render("transaction/newtransaction.html.twig", array("form" => $form->createView(), "page" => "addtransaction"));
    }

    /**
     * @Route("/transaction/make/{customerid}", name="maketransaction", requirements={"customerid":"\d+"})
     */
    public function makeTransaction(Request $request, $customerid = null) {
        $this->denyAccessUnlessGranted('ROLE_SALES_PERSONNEL');
        return $this->forward("App\Controller\TransactionController::addTransaction", array('cuid' => $customerid));
        // replace this line with your own code!
    }

    /**
     * @Route("/transaction/view/{transid}/{id}", name="viewtransaction", requirements={"transid":"\d+|last-customer|last-order", "id":"\d+"})
     */
    public function viewTransaction(Request $request, $transid, $id = null) {
        $this->denyAccessUnlessGranted('ROLE_SALES_PERSONNEL');
        // replace this line with your own code!
        $arrdata = array("page" => "viewtransaction");

        $em = $this->getDoctrine();
        $rep = $em->getRepository(Transaction::class);
        if ($transid == 'last-customer') {
            $transaction = $rep->findLastTransaction($id, 'customer');
        } else if ($transid == 'last-order') {
            $transaction = $rep->findLastTransaction($id, 'order');
        } else {
            $transaction = $rep->find($transid);
            if (!$transaction) {
                $transaction = $rep->findByTid($transid);
            }
        }

        if ($transaction) {
            if (is_array($transaction)) {
                $transaction = $transaction[0];
            }
            $arrdata['transaction'] = $transaction;
            $amtpaidbefore = $rep->getAmtPaidBefore($transaction->getId(), $transaction->getOrder()->getId());
            $amtpaidafter = $rep->getAmtPaidAfter($transaction->getId(), $transaction->getOrder()->getId());
            $arrdata['amtpaidbefore'] = $amtpaidbefore[0]['sumamt'];
            $arrdata['amtpaidafter'] = $amtpaidafter[0]['sumamt'];
        }
        $form = $this->createFormBuilder(new \App\Utility\TransactionFind())
                ->setAction($this->generateUrl('vtransaction'))
                ->add('tid', TextType::class, array("label" => "Transaction ID:", "data" => ($transaction) ? $transaction->getTid() : "", "attr" => array("placeholder" => "Transaction ID")))
                ->add('Load', SubmitType::class, array('label' => 'Load'))
                ->getForm();
        if (!$transaction) {
            $form->addError(new \Symfony\Component\Form\FormError("Transaction record not found!"));
        }

        $arrdata['form'] = $form->createView();



        return $this->render("transaction/viewtransaction.html.twig", $arrdata);
    }

    /**
     * @Route("/transaction/view", name="vtransaction")
     */
    public function vTransaction(Request $request) {
        $this->denyAccessUnlessGranted('ROLE_SALES_PERSONNEL');
        // replace this line with your own code!
        $form = $this->createFormBuilder(new \App\Utility\TransactionFind())
                ->setAction($this->generateUrl('vtransaction'))
                ->add('tid', TextType::class, array("label" => "Transaction ID:", "attr" => array("placeholder" => "Transaction ID")))
                ->add('Load', SubmitType::class, array('label' => 'Load'))
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $transactionid = $form->get('tid')->getData();
            return $this->forward('App\Controller\TransactionController::viewTransaction', array("transid" => $transactionid));
        }
        return $this->render("transaction/viewtransaction.html.twig", array("form" => $form->createView(), "page" => "viewtransaction"));
    }

    /* Utilities */
}
