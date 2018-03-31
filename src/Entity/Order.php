<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OrderRepository")
 * @UniqueEntity(fields="oid", message="Order Id must be unique")
 * @ORM\Table(name="salesorder")
 */
class Order {

    use \App\Utility\Utils;

    public function __construct() {
        $this->transactions = new ArrayCollection();
        $dt = new \DateTime();
        $this->orderDate = $dt;
        $this->dateRecorded = $dt;
        $this->orderStatus = "active";
        $this->quantityDelivered=0;
        $this->orderDeliveryStatus="not-delivered";
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name= "order_id", type="integer")
     */
    private $id;
    // add your own fields

    /**
     *
     * @ORM\Column(type="string", length=14, nullable=false, unique=true)
     */
    private $oid;

    /**
     * 
     * @Assert\NotBlank(message="Select a product")
     * @ORM\ManyToOne(targetEntity="App\Entity\Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="product_id", nullable=false)
     */
    private $product;

    /**
     *
     * @Assert\NotBlank(message="Invalid date of order")
     * @Assert\DateTime(message = "Order Date must be a valid date with time.")
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $orderDate;

    /**
     *
     * @Assert\NotBlank(message="Date of entry unknown!")
     * @Assert\DateTime(message = "Date recorded must be a valid date with time.")
     * @Assert\GreaterThanOrEqual(propertyPath="orderDate", message="Date Recorded must be a date on or after the actual order date.")
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $dateRecorded;

    /**
     *
     * @Assert\NotBlank(message = "Specify the quantity")
     * @Assert\Type(type="numeric", message="Quantity must be a numeric value.")
     * @Assert\GreaterThan(value=0, message="Quantity must be greater than 0.")
     * @ORM\Column(type="integer", nullable=false, options={"unsigned":true})
     */
    private $quantity;
    
    /**
     *
     * @Assert\Type(type="numeric", message="Quantity Delivered must be a numeric value.")
     * @Assert\LessThanOrEqual(propertyPath="quantity", message="Quantity delivered must be less than or equal to quantity ordered.")
     * @ORM\Column(type="integer", nullable=false, options={"unsigned":true, "default":0})
     */
    private $quantityDelivered;

    /**
     *
     * @Assert\NotBlank(message="Specify the unit price")
     * @Assert\Type(type="numeric", message="Unit Price must be a numeric value.")
     * @Assert\GreaterThanOrEqual(value=0, message="Unit Price must be greater than or equal to 0.")
     * @ORM\Column(type="decimal", precision=15, scale=2, nullable=false, options={"unsigned":true})
     */
    private $unitPrice;

    /**
     * 
     * @Assert\NotBlank(message="Select a customer/agent")
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer", inversedBy="orders")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="customer_id", nullable=false)
     */
    private $customer;

    /**
     * 
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="order_placed_by", referencedColumnName="user_id", nullable=false)
     */
    private $salesPerson;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Transaction", mappedBy="order")
     */
    private $transactions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Refund", mappedBy="order")
     */
    private $refunds;

    /**
     * @Assert\Choice(callback= "getUtilOrderStatus", message="Invalid Order Status")
     * @ORM\Column(type="string", nullable=false, length=20, options={"default":"active"})
     */
    private $orderStatus;

    /**
     * @Assert\Choice(callback= "getUtilOrderDeliveryStatus", message="Invalid Order Delivery Status")
     * @ORM\Column(type="string", nullable=false, length=20, options={"default":"not-delivered"})
     */
    private $orderDeliveryStatus;

    /**
     * 
     * @ORM\Column(type="string", nullable=true, length=500)
     */
    private $deliveryLocation;

    /**
     *
     * @Assert\GreaterThanOrEqual(propertyPath="orderDate", message="Date of closure must be on or after order date.")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateClosed;

    /**
     *
     * @ORM\Column(type="string", nullable=true, length=300)
     */
    private $closingRemark;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="order_closed_by", referencedColumnName="user_id", nullable=true)
     */
    private $closedBy;

    public function getId() {
        return $this->id;
    }

    public function getOid() {
        return $this->oid;
    }

    public function setOid($oid) {
        $this->oid = $oid;
    }

    public function getDateRecorded() {
        return $this->dateRecorded;
    }

    public function setDateRecorded($daterecorded) {
        $this->dateRecorded = $daterecorded;
    }

    public function getOrderDate() {
        return $this->orderDate;
    }

    public function setOrderDate($odate) {
        $this->orderDate = $odate;
    }

    public function getQuantity() {
        return $this->quantity;
    }

    public function setQuantity($qty) {
        $this->quantity = $qty;
    }

    public function getUnitPrice() {
        return $this->unitPrice;
    }

    public function setUnitPrice($uprice) {
        $this->unitPrice = $uprice;
    }

    public function getCustomer() {
        return $this->customer;
    }

    public function setCustomer($customer) {
        $this->customer = $customer;
    }

    public function getSalesPerson() {
        return $this->salesPerson;
    }

    public function setSalesPerson($sperson) {
        $this->salesPerson = $sperson;
    }

    public function getOrderStatus() {
        return $this->orderStatus;
    }

    public function setOrderStatus($ostatus) {
        if (!in_array($ostatus, Order::getUtilOrderStatus())) {
            throw new \InvalidArgumentException("Invalid Order Status");
        }
        $this->orderStatus = $ostatus;
    }
    
    public function getOrderDeliveryStatus() {
        return $this->orderDeliveryStatus;
    }
    
    public function setOrderDeliveryStatus($ostatus) {
        if (!in_array($ostatus, Order::getUtilOrderDeliveryStatus())) {
            throw new \InvalidArgumentException("Invalid Order Delivery Status");
        }
        $this->orderDeliveryStatus = $ostatus;
    }

    public function getDateClosed() {
        return $this->dateClosed;
    }

    public function setDateClosed($dtclosed) {
        $this->dateClosed = $dtclosed;
    }

    public function getClosingRemark() {
        return $this->closingRemark;
    }

    public function setClosingRemark($cremark) {
        $this->closingRemark = $cremark;
    }

    public function getClosedBy() {
        return $this->closedBy;
    }

    public function setClosedBy($closedby) {
        $this->closedBy = $closedby;
    }

    public function getProduct() {
        return $this->product;
    }

    public function setProduct($product) {
        $this->product = $product;
    }

    /**
     * @return Collection|Transaction[]
     */
    public function getTransactions() {
        return $this->transactions;
    }

    public function addTransaction($transaction) {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions[] = $transaction;
            $transaction->setOrder($this);
        }
    }

    /**
     * @return Collection|Refund[]
     */
    public function getRefunds() {
        return $this->refunds;
    }

    public function addRefund($refund) {

        if (!$this->refunds->contains($refund)) {
            $this->refunds[] = $refund;
            $refund->setOrder($this);
        }
    }

    /*     * Utility */

    public function getAmountPayable() {
        return ($this->unitPrice * $this->quantity);
    }

    public function getAmountPaid() {
        $amtpaid = 0;
        foreach ($this->transactions as $trxn) {
            $amtpaid += $trxn->getAmountPaid();
        }

        return $amtpaid;
    }

    public function getAmountDue() {
        $amtdue = 0;
        $amtdue = $this->getAmountPayable() - $this->getAmountPaid();
        return ($amtdue < 0) ? (0) : ($amtdue);
    }

    public function getAmountDueAfterTransaction($transaction) {
        $amt = $this->getAmountPaidAfterTransaction($transaction);
        $amt = $this->getAmountPayable() - $amt;
        return ($amt < 0) ? (0) : ($amt);
    }

    public function getAmountPaidAfterTransaction($transaction) {
        $amt = 0;
        foreach ($this->transactions as $trxn) {
            if ($transaction->getTransDate() > $trxn->getTransDate()) {
                $amt += $trxn->getAmountPaid();
            } else
            if ($transaction->getTransDate() == $trxn->getTransDate()) {
                if ($transaction->getId() > $trxn->getId()) {
                    $amt += $trxn->getAmountPaid();
                }
            }
        }
        $amt += $transaction->getAmountPaid();

        return ($amt < 0) ? (0) : ($amt);
    }

    public function getLastTransaction() {
        $lasttrxn = null;
        foreach ($this->transactions as $trxn) {
            if (!isset($lasttrxn)) {
                $lasttrxn = $trxn;
            }
            if ($lasttrxn->getTransDate() < $trxn->getTransDate()) {
                $lasttrxn = $trxn;
            }
            if ($lasttrxn->getTransDate() == $trxn->getTransDate()) {
                if ($lasttrxn->getId() < $trxn->getId()) {
                    $lasttrxn = $trxn;
                }
            }
        }

        return $lasttrxn;
    }
    
    public function getQuantityDelivered(){
        return $this->quantityDelivered;
    }
    public function setQuantityDelivered($quantity){
        $this->quantityDelivered = $quantity;
        if($quantity == 0){
            $this->setOrderDeliveryStatus("not-delivered");                    
        }else if($quantity < $this->quantity){
            $this->setOrderDeliveryStatus("partial-delivery"); 
        }else{
            $this->setOrderDeliveryStatus("delivered"); 
        }
    }
    
    public function getDeliveryLocation(){
        return $this->deliveryLocation;
    }
    
    public function setDeliveryLocation($loc){
        $this->deliveryLocation = $loc;
    }

}
