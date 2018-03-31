<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\{
    Response,
    Request
};
use Swift_Mailer;
use App\Entity\{
    ProductDistribution,
    LoadingRecord
};
use \App\Form\ProductDistributionType;

class ProductDistributionController extends Controller {

    /**
     * @Route("/product/distribution", name="product_distribution")
     */
    public function index() {
        // replace this line with your own code!
        return $this->render('@Maker/demoPage.html.twig', ['path' => str_replace($this->getParameter('kernel.project_dir') . '/', '', __FILE__)]);
    }

    /**
     * @Route("/product/distribution/list_ajax/{loadingrecordid}", name="list_product_distribution", requirements={"loadingrecordid":"\d+"})
     */
    public function listProductDistribution(Request $request, $loadingrecordid) {
        if ($request->isXmlHttpRequest()) {
            $doct = $this->getDoctrine();
            $loadingrecord = $doct->getRepository(LoadingRecord::class)->find($loadingrecordid);
            if (null === $loadingrecord) {
                return new Response('Invalid Loading Record!');
            }
            return $this->render("loadingrecord/listproductdistribution_ajax.html.twig", array("loading" => $loadingrecord));
        } else {
            return new Response("Invalid request!");
        }
    }

    /**
     * @Route("/product/distribution/add/{loadingrecordid}", name="add_product_distribution", requirements={"loadingrecordid":"\d+"})
     */
    public function addProductDistribution(Request $request, Swift_Mailer $mailer, $loadingrecordid) {
        if (/* true || */$request->isXmlHttpRequest()) {
            $viewoptions = array("page" => "addloadingrecord");
            $doct = $this->getDoctrine();
            $rep = $doct->getRepository(ProductDistribution::class);
            $pd = new ProductDistribution();
            $pd->setDateRecorded(new \DateTime());
            $pd->setCreatedBy($this->getUser());
            $options = array();
            $loadingrecord = $doct->getRepository(LoadingRecord::class)->find($loadingrecordid);
            if (null === $loadingrecord) {
                return new Response('error_1');
            }
            if ($loadingrecord->getLoadingStatus() === "loading") {
                return new Response('error_2');
            }

            $options['loadingrecord'] = $loadingrecord;
            $viewoptions['loadingrecord'] = $loadingrecord;
            $options['action'] = $this->generateUrl("add_product_distribution", array("loadingrecordid" => $loadingrecordid));
            $options['repo']= $rep;
            
            $form = $this->createForm(ProductDistributionType::class, $pd, $options);
            $form->handleRequest($request);
            //return new Response($request->getContent());
            $pd->setLoadingRecord($loadingrecord);
            if ($form->isSubmitted() && $form->isValid()) {
                //return new Response("is valid");
                $haserror = false;
                
                if ($pd->getDateRecorded()->diff($pd->getDeliveryDate())->format('%rs') < 0) {
                    $haserror = true;
                    $form->get('deliveryDate')->addError(new \Symfony\Component\Form\FormError("Delivery date cannot be a date in the future"));
                }
                if ($pd->getQuantityDelivered() <= 0) {
                    $haserror = true;
                    $form->get("quantityDelivered")->addError(new \Symfony\Component\Form\FormError("Provide quantity of product delivered."));
                }
                if (($loadingrecord->getDistributedQuantity() + $pd->getQuantityDelivered()) > $loadingrecord->getDeliveredQuantity()) {
                    $haserror = true;
                    $form->get("quantityDelivered")->addError(new \Symfony\Component\Form\FormError("Total quantity delivered (" . number_format(($loadingrecord->getDistributedQuantity() + $pd->getQuantityDelivered())) . ") will exceed total quantity (" . number_format($loadingrecord->getDeliveredQuantity()) . ") in loaded truck (" . $loadingrecord->getTruckNo() . ")"));
                }
                if ($pd->getLocation() == "") {
                    $haserror = true;
                    $form->get("locationname")->addError(new \Symfony\Component\Form\FormError("Specify location of delivery."));
                }
                if (!$haserror) {
                    //return new Response("about to persist and flush");
                    try {
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($pd);
                        $em->flush();
                    } catch (Exception $e) {
                        return new Response("error_3");
                    }
                    return new Response('0');
                }
            }
            
            $viewoptions['form'] = $form->createView();
            return $this->render("loadingrecord/newproductdistribution_ajax.html.twig", $viewoptions);
        }
    }

    /**
     * @Route("/product/distribution/del/{distid}", name="del_product_distribution", requirements={"distid":"\d+"})
     */
    public function delProductDistribution(Request $request, $distid) {
        if (/* true || */$request->isXmlHttpRequest()) {
            $doct = $this->getDoctrine();
            $dist = $doct->getRepository(ProductDistribution::class)->find($distid);
            if (null === $dist) {
                return new Response('error_1');
            }
            try {
                $dist->getOrder()->setQuantityDelivered($dist->getOrder()->getQuantityDelivered()-$dist->getQuantityDelivered());
                $mgr = $doct->getManager();
                $mgr->remove($dist);
                $mgr->flush();
            } catch (Exception $e) {
                return new Response("error_3");
            }
            return new Response('0');
        }
    }

}
