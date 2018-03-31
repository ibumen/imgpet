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
use App\Form\ExpenseType;
use App\Entity\{
    Truck,
    Expense
};
Use Doctrine\ORM\EntityManagerInterface;

class ExpenseController extends Controller {

    use \App\Utility\Utils;

    /**
     * @Route("/expense/list", name="listexpense")
     */
    public function listExpenses() {
        //$this->denyAccessUnlessGranted('ROLE_PERSONNEL_SALES');
        $arrdata = array("page" => "listexpense");
        $em = $this->getDoctrine()->getRepository(\App\Entity\Expense::class);
            $expenses = $em->findAll();
        
        $arrdata['expenses'] = $expenses;
        return $this->render("expense/listexpense.html.twig", $arrdata);
    }

    /**
     * @Route("/expense/add", name="addexpense")
     */
    public function addExpense(Request $request) {
        //$this->denyAccessUnlessGranted('ROLE_PERSONNEL_SALES');
        // replace this line with your own code!
        $expense = new Expense();

        $doct = $this->getDoctrine();
        $expense->setEnteredBy($this->getUser());

        $form = $this->createForm(ExpenseType::class, $expense, array());
        //echo $tid; exit();
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            $em = $doct->getManager();
            $em->persist($expense);
            $em->flush();

            //$this->addFlash("registrationsuccess", "Truck expense was added!");
            //$form = $this->createForm(TransactionType::class, new Transaction());
            return $this->redirectToRoute("viewexpense", array('expenseid' => $expense->getId()));
        }

        return $this->render("expense/newexpense.html.twig", array("form" => $form->createView(), "page" => "addexpense"));
    }

    /**
     * @Route("/expense/edit/{expenseid}", name="editexpense", requirements={"expenseid":"\d+"})
     */
    public function editExpense(Request $request, $expenseid = null) {
        //$this->denyAccessUnlessGranted('ROLE_PERSONNEL_SALES');
        // replace this line with your own code!

        $doct = $this->getDoctrine();
        $rep = $doct->getRepository(Expense::class);

        $expense = $rep->find($expenseid);

        $form = $this->createForm(ExpenseType::class, $expense, array());
        //echo $tid; exit();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $doct->getManager();
            $em->persist($expense);
            $em->flush();

            //$this->addFlash("modificationsuccess", "Truck expense was modified!");
            //$form = $this->createForm(TransactionType::class, new Transaction());
            return $this->redirectToRoute("viewexpense", array('expenseid' => $expense->getId()));
        }

        return $this->render("expense/editexpense.html.twig", array("form" => $form->createView(), "page" => "listexpense"));
    }

    /**
     * @Route("/expense/view/{expenseid}", name="viewexpense", requirements={"expenseid":"\d+"})
     */
    public function viewExpense(Request $request, $expenseid) {
        //$this->denyAccessUnlessGranted('ROLE_PERSONNEL_SALES');
        // replace this line with your own code!
        $arrdata = array("page" => "viewexpense");

        $em = $this->getDoctrine();
        $rep = $em->getRepository(Expense::class);
        $expense = $rep->find($expenseid);
        if (!$expense) {
            return $this->redirectToRoute("listexpense");
        }

        if (is_array($expense)) {
            $expense = $expense[0];
        }
        $arrdata['expense'] = $expense;

        return $this->render("expense/viewexpense.html.twig", $arrdata);
    }

    /**
     * @Route("/expense/del/{expenseid}", name="del_expense", requirements={"expenseid":"\d+"})
     */
    public function delExpense(Request $request, $expenseid) {
        if (/* true || */$request->isXmlHttpRequest()) {
            $doct = $this->getDoctrine();
            $expense = $doct->getRepository(Expense::class)->find($expenseid);
            if (null === $expense) {
                return new Response('error_1');
            }
            try {
                $mgr = $doct->getManager();
                $mgr->remove($expense);
                $mgr->flush();
            } catch (Exception $e) {
                return new Response("error_3");
            }
            return new Response('0');
        }
    }

    /* Utilities */
}
