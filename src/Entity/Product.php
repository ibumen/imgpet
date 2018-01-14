<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 */
class Product {
    use \App\Utility\Utils;    

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="product_id", type="integer")
     */
    private $id;

    // add your own fields

    /**
     *
     * @Assert\NotBlank()
     * @ORM\Column(type="string", nullable=false, unique=true, length=30)
     */
    private $productName;

    /**
     *
     * @Assert\NotBlank()
     * @Assert\Type(type="numeric", message="Unit Price must be a numeric value.")
     * @Assert\GreaterThan(value=0, message="Unit Price must be greater than 0.")
     * @ORM\Column(type="decimal", precision=15, scale=2, nullable=false, options={"unsigned":true})
     */
    private $unitPrice;

    /**
     *
     * @Assert\NotBlank()
     * @Assert\Choice(callback="getUtilProductQuantityMetrics", message="Invalid Metric")
     * @ORM\Column(type="string", nullable=false, length=30, options={"default":"litre"})
     */
    private $unitMetric;

    /**
     *
     * @Assert\NotBlank()
     * @ORM\Column(type="string", nullable=false, length=10)
     */
    private $productCode;

    //functions

    public function getId() {
        return $this->id;
    }

    public function getProductName() {
        return $this->productName;
    }

    public function setProductName($pname) {
        $this->productName = trim($pname);
    }

    public function getUnitPrice() {
        return $this->unitPrice;
    }

    public function setUnitPrice($uprice) {
        $this->unitPrice = $uprice;
    }

    public function getUnitMetric() {
        return $this->unitMetric;
    }

    public function setUnitMetric($umetric) {
        $umetric = trim(strtolower($umetric));
        if (!in_array($umetric, Product::getUtilProductQuantityMetrics())) {
            throw new \InvalidArgumentException("Invalid Metric");
        }
        $this->unitMetric= $umetric;
    }

    public function getProductCode() {
        return $this->productCode;
    }

    public function setProductCode($pcode) {
        $this->productCode = trim($pcode);
    }

}
