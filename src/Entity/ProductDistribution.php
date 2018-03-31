<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProductDistributionRepository")
 */
class ProductDistribution {

    use \App\Utility\Utils;

    public function __construct() {
        $dt = new \DateTime();
        $this->dateRecorded = $dt;
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     *
     * @Assert\NotBlank(message="Unknown date of entry.")
     * @Assert\DateTime(message = "Date of entry must be a valid date with time.")
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $dateRecorded;

    /**
     * 
     * @Assert\NotBlank(message="Unknown date of delivery.")
     * @Assert\DateTime(message = "Delivery date must be a valid date with time.")
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $deliveryDate;

    /**
     *
     * @Assert\NotBlank(message="Please select truck loaded")
     * @ORM\ManyToOne(targetEntity="App\Entity\LoadingRecord", inversedBy="productDistributions")
     * @ORM\JoinColumn(name="loading_record_id", referencedColumnName="loading_record_id", nullable=false)
     */
    private $loadingRecord;

    /**
     *
     * @Assert\NotBlank(message = "Specify the quantity delivered")
     * @Assert\Type(type="numeric", message="Quantity must be a numeric value.")
     * @Assert\GreaterThan(value=0, message="Quantity must be greater than 0.")
     * @ORM\Column(type="integer", nullable=false, options={"unsigned":true})
     */
    private $quantityDelivered;

    /**
     * 
     * @Assert\NotBlank(message = "Specify delivery location")
     * @ORM\Column(type="string", length=150, nullable=false)
     */
    private $location;

    /**
     *
     * @Assert\NotBlank(message="Please select order")
     * @ORM\ManyToOne(targetEntity="App\Entity\Order")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="order_id", nullable=false)
     */
    private $order;

    /**
     * 
     * @Assert\NotBlank(message="Select User")
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="user_id", nullable=false)
     */
    private $createdBy;

    public function getId() {
        return $this->id;
    }

    public function getDateRecorded() {
        return $this->dateRecorded;
    }

    public function setDateRecorded($daterecorded) {
        $this->dateRecorded = $daterecorded;
    }

    public function getDeliveryDate() {
        return $this->deliveryDate;
    }

    public function setDeliveryDate($dt) {
        $this->deliveryDate = $dt;
    }

    public function getLocation() {
        return $this->location;
    }

    public function setLocation($loc) {
        $this->location = $loc;
    }

    public function getCreatedBy() {
        return $this->createdBy;
    }

    public function setCreatedBy($sperson) {
        $this->createdBy = $sperson;
    }

    public function getQuantityDelivered() {
        return $this->quantityDelivered;
    }

    public function setQuantityDelivered($qty) {
        $this->quantityDelivered = $qty;
        $this->updateOrder();
    }

    public function getLoadingRecord() {
        return $this->loadingRecord;
    }

    public function setLoadingRecord($loading) {
        $this->loadingRecord = $loading;
    }

    public function setOrder($order) {
        $this->order = $order;
        $this->updateOrder();
    }

    public function getOrder() {
        return $this->order;
    }

    //non mapped function
    public function updateOrder() {
        if (isset($this->order)) {
            $this->order->setQuantityDelivered($this->order->getQuantityDelivered() + $this->getQuantityDelivered());
        }
    }

}
