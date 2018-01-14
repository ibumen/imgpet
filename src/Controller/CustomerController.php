<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\{
    Response,
    Request
};
use Symfony\Component\Form\Extension\Core\Type\{
    TextType,
    SubmitType
};
use App\Form\CustomerType;
use App\Entity\{Customer, Order, Transaction};
Use Doctrine\ORM\EntityManagerInterface;

class CustomerController extends Controller {

    /**
     * @Route("/customer/list", name="listcustomer")
     */
    public function listCustomer() {
        $this->denyAccessUnlessGranted('ROLE_SALES_PERSONNEL');
        $em = $this->getDoctrine()->getRepository(\App\Entity\Customer::class);
        $customers = $em->findAll();
        return $this->render("customer/listcustomer.html.twig", array("page" => "listcustomer", "customers" => $customers));
    }

    /**
     * @Route("/customer/add", name="addcustomer")
     */
    public function addCustomer(Request $request) {
        $this->denyAccessUnlessGranted('ROLE_SALES_PERSONNEL');
        // replace this line with your own code!
        $customer = new Customer();
        $form = $this->createForm(CustomerType::class, $customer);

        $cid = "C";
        $customer->setDateReg(new \DateTime());
        $doct = $this->getDoctrine();
        $lastcustomer = $doct->getRepository(Customer::class)->findOneByDateReg();
        $lastDate = $lastcustomer[0]->getDateReg();

        if ($customer->getDateReg()->diff($lastDate)->format("%d") > 0) {
            $cid .= $customer->getDateReg()->format("Ymd") . "001";
        } else {
            $dtstr = $lastDate->format("Ymd");
            $lcid = $lastcustomer[0]->getCid();
            $sn = substr($lcid, 9);
            $sn = $sn + 1;
            $cid .= $dtstr . (($sn < 10) ? ("00$sn") : (($sn < 100) ? ("0$sn") : ($sn)));
        }
        $customer->setCid($cid);
        //echo $cid; exit();
        $form->handleRequest($request);
        

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $doct->getManager();
            $em->persist($customer);
            $em->flush();
            $this->addFlash("registrationsuccess", "Customer registration was successful!");
            $form = $this->createForm(CustomerType::class, new Customer());
        }

        return $this->render("customer/newcustomer.html.twig", array("form" => $form->createView(), "page" => "addcustomer"));
    }

    /**
     * @Route("/customer/edit/{customerid}", name="editcustomer", requirements={"customerid":"\d+"})
     */
    public function editCustomer(EntityManagerInterface $emi, Request $request, $customerid) {
        $this->denyAccessUnlessGranted('ROLE_SALES_PERSONNEL');
        $customer = $emi->getRepository(Customer::class)->find($customerid);
        if (!$customer && null === $customer) {
            throw new \InvalidArgumentException("Customer does not exist!");
        }

        $form = $this->createForm(CustomerType::class, $customer);

        $form->handleRequest($request);
        $arrdata = array("page" => "editcustomer");
        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($customer);
            $em->flush();
            $this->addFlash("modificationsuccess", "Customer modification was successful!");
            $arrdata['customerid'] = $customer->getId();
        }

        $arrdata["form"] = $form->createView();

        return $this->render("customer/editcustomer.html.twig", $arrdata);
    }

    /**
     * @Route("/customer/view/{customerid}", name="viewcustomer", requirements={"customerid":"\d+"})
     */
    public function viewCustomer(Request $request, $customerid) {
        // replace this line with your own code!
        $this->denyAccessUnlessGranted('ROLE_SALES_PERSONNEL');
        $arrdata = array("page" => "viewcustomer");

        $em = $this->getDoctrine();
        $rep = $em->getRepository(Customer::class);
        $customer = $rep->find($customerid);
        if (!$customer) {
            $customer = $rep->findByCid($customerid);
        }

        if ($customer) {
            if (is_array($customer)) {
                $customer = $customer[0];
                $customerid = $customer->getId();
            }
            $arrdata['customer'] = $customer;
            $orderrep = $em->getRepository(Order::class);
            $lastorder = $orderrep->findLastOrder($customerid);
            $unsettledorder = $orderrep->findAllStatusOrder("active",$customerid);
            $arrdata['lastorder']=(count($lastorder))?$lastorder[0]:null;
            $arrdata['unsettledorder']=$unsettledorder;
            $trxnrep = $em->getRepository(Transaction::class);
            $lasttrxn = $trxnrep->findLastTransaction($customerid);
            $arrdata['lasttrxn']=(count($lasttrxn))?$lasttrxn[0]:null;
        }
        $form = $this->createFormBuilder(new \App\Utility\CustomerFind())
                ->setAction($this->generateUrl('vcustomer'))
                ->add('cid', TextType::class, array("label" => "Customer Unique ID:", "data" => ($customer) ? $customer->getCid() : "", "attr" => array("placeholder" => "Customer Unique ID")))
                ->add('Load', SubmitType::class, array('label' => 'Load'))
                ->getForm();
        if (!$customer) {
            $form->addError(new \Symfony\Component\Form\FormError("Customer record not found!"));
        }

        $arrdata['form'] = $form->createView();



        return $this->render("customer/viewcustomer.html.twig", $arrdata);
    }

    /**
     * @Route("/customer/view", name="vcustomer")
     */
    public function vCustomer(Request $request) {
        // replace this line with your own code!
        $this->denyAccessUnlessGranted('ROLE_SALES_PERSONNEL');
        $form = $this->createFormBuilder(new \App\Utility\CustomerFind())
                ->setAction($this->generateUrl('vcustomer'))
                ->add('cid', TextType::class, array("label" => "Customer Unique ID:", "attr" => array("placeholder" => "Customer Unique ID")))
                ->add('Load', SubmitType::class, array('label' => 'Load'))
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $customerid = $form->get('cid')->getData();
            return $this->forward('App\Controller\CustomerController::viewCustomer', array("customerid" => $customerid));
        }
        return $this->render("customer/viewcustomer.html.twig", array("form" => $form->createView(), "page" => "viewcustomer"));
    }

    /* Utilities */
}
