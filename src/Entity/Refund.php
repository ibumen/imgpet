<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RefundRepository")
 */
class Refund {
    use \App\Utility\Utils;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="refund_id", type="integer")
     */
    private $id;
    // add your own fields

    /**
     *
     * @Assert\NotBlank()
     * @Assert\DateTime(message = "Refund Date must be a valid date with time.")
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $refundDate;

    /**
     *
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="App\Entity\Order", inversedBy="refunds")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="order_id", nullable=false)
     */
    private $order;

    /**
     *
     * @Assert\NotBlank()
     * @Assert\Type(type="numeric", message="Amount Paid must be a numeric value.")
     * @Assert\GreaterThan(value=0, message="Amount Paid must be greater than 0.")
     * @ORM\Column(type="decimal", precision=15, scale=2, nullable=false)
     */
    private $amountPaid;
    
    /**
     * 
     * @Assert\NotBlank()
     * @Assert\Choice(callback="getUtilPaymentOptions", message="Invalid Refund Method")
     * @ORM\Column(type="string", nullable = false)
     */
    private $refundMethod;
    
    /**
     * 
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="committed_by", referencedColumnName="user_id", nullable=false)
     */
    private $committedBy;

    public function getId() {
        return $this->id;
    }

    public function getRefundDate() {
        return $this->refundDate;
    }

    public function setRefundDate($refundDate) {
        $this->refundDate = $refundDate;
    }

    public function getOrder(): Order {
        return $this->order;
    }

    public function setOrder(Order $order) {
        $this->order = $order;
    }

    public function getAmountPaid() {
        return $this->amountPaid;
    }

    public function setAmountPaid($amtpaid) {
        $this->amountPaid = $amtpaid;
    }

    public function getCommittedBy(): \App\Entity\User {
        return $this->committedBy;
    }

    public function setCommittedBy(\App\Entity\User $sperson) {
        $this->committedBy = $sperson;
    }
    
    public function getRefundMethod(){
        return $this->refundMethod;
    }
    
    public function setRefundMethod($rmethod){
        $rmethod= trim(strtolower($rmethod));
        if(!in_array($rmethod, Refund::getUtilPaymentOptions())){
            throw new \InvalidArgumentException("Invalid Refund Method!");
        }
        $this->refundMethod= $rmethod;
    }

}
