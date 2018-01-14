<?php

namespace App\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\{
    Response,
    Request
};
use App\Form\ProductType;
use App\Entity\Product;

class ProductController extends Controller {

    /**
     * 
     * @Route("/product/list", name="listproduct")
     */
    public function listProduct() {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em = $this->getDoctrine()->getRepository(\App\Entity\Product::class);
        $products = $em->findAll();
        return $this->render("product/listproduct.html.twig", array("page" => "listproduct", "products" => $products));
    }

    /**
     * 
     * @Route("/product/add", name="addproduct")
     */
    public function addProduct(Request $request) {
        // replace this line with your own code!
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();
            $this->addFlash("registrationsuccess", "Product registration was successful!");
            $form = $this->createForm(ProductType::class, new Product());
        }

        return $this->render("product/newproduct.html.twig", array("form" => $form->createView(), "page" => "addproduct"));
    }
    
    /**
     * 
     * @Route("/product/edit/{productid}", name="editproduct", requirements={"productid":"\d+"})
     */
    public function editProduct(Request $request, $productid) {
        return;
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        // replace this line with your own code!
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();
            $this->addFlash("registrationsuccess", "Product registration was successful!");
            $form = $this->createForm(ProductType::class, new Product());
        }

        return $this->render("product/newproduct.html.twig", array("form" => $form->createView(), "page" => "addproduct"));
    }

}
