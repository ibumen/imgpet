<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TransactionRepository")
 * @UniqueEntity(fields="tid", message="Transaction Id must be unique")
 * @ORM\Table(name="salestransaction")
 */
class Transaction {

    use \App\Utility\Utils;

    public function __construct() {
        $dt = new \DateTime();
        $this->dateRecorded = $dt;
        $this->transDate = $dt;
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="transaction_id", type="integer")
     */
    private $id;
    // add your own fields

    /**
     *
     * @ORM\Column(type="string", length=15, nullable=false, unique=true)
     */
    private $tid;

    /**
     *
     * @Assert\NotBlank(message="Transaction Date must be specified.")
     * @Assert\DateTime(message = "Transaction Date must be a valid date with time.")
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $transDate;

    /**
     *
     * @Assert\NotBlank(message="Unknown date of entry.")
     * @Assert\DateTime(message = "Date recorded must be a valid date with time.")
     * @Assert\GreaterThanOrEqual(propertyPath="transDate", message="Date Recorded must be a date on or after the actual transaction date.")
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $dateRecorded;

    /**
     *
     * @Assert\NotBlank(message="Please select order")
     * @ORM\ManyToOne(targetEntity="App\Entity\Order", inversedBy="transactions")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="order_id", nullable=false)
     */
    private $order;

    /**
     *
     * @Assert\NotBlank(message="Invalid amount paid.")
     * @Assert\Type(type="numeric", message="Amount Paid must be a numeric value.")
     * @Assert\GreaterThan(value=0, message="Amount Paid must be greater than 0.")
     * @ORM\Column(type="decimal", precision=15, scale=2, nullable=false)
     */
    private $amountPaid;

    /**
     * @Assert\NotBlank(message="Select Payment Method")
     * @Assert\Choice(callback="getUtilPaymentOptions", message="Invalid Payment Method")
     * @ORM\Column(type="string", nullable = false)
     */
    private $paymentMethod;

    /**
     * 
     * @Assert\NotBlank(message="select user")
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="committed_by", referencedColumnName="user_id", nullable=false)
     */
    private $committedBy;

    public function getId() {
        return $this->id;
    }

    public function getTid() {
        return $this->tid;
    }

    public function setTid($tid) {
        $this->tid = $tid;
    }

    public function getTransDate() {
        return $this->transDate;
    }

    public function setTransDate($transDate) {
        $this->transDate = $transDate;
    }

    public function getDateRecorded() {
        return $this->dateRecorded;
    }

    public function setDateRecorded($daterecorded) {
        $this->dateRecorded = $daterecorded;
    }

    public function getOrder() {
        return $this->order;
    }

    public function setOrder($order) {
        $this->order = $order;
    }

    public function getAmountPaid() {
        return $this->amountPaid;
    }

    public function setAmountPaid($amtpaid) {
        $this->amountPaid = $amtpaid;
    }

    public function getCommittedBy() {
        return $this->committedBy;
    }

    public function setCommittedBy($sperson) {
        $this->committedBy = $sperson;
    }

    public function getPaymentMethod() {
        return $this->paymentMethod;
    }

    public function setPaymentMethod($pmethod) {
        $pmethod = trim(strtolower($pmethod));
        if (!in_array($pmethod, Transaction::getUtilPaymentOptions())) {
            throw new \InvalidArgumentException("Invalid Payment Method!");
        }
        $this->paymentMethod = $pmethod;
    }

    public function getData() {
        $data = array();
        if (isset($this->id)) {
            $data['id'] = $this->id;
        }
        if (isset($this->tid)) {
            $data['tid'] = $this->tid;
        }
        $data['transDate'] = $this->transDate;
        $data['order'] = $this->order;
        $data['paymentMethod'] = $this->paymentMethod;
        $data['committedBy'] = $this->committedBy;
        $data['amountPaid'] = $this->amountPaid;
        $data['dateRecorded'] = $this->dateRecorded;
        return $data;
    }

//    public function getAmtBefore($tid, $oid) {
//        return $this->createQueryBuilder('t')->select('sum(t.amountPaid)')
//                        ->where('t.id < :tid')
//                        ->andWhere('t.order= :oid')
//                        ->setParameter('tid', $tid)
//                        ->setParameter('oid', $oid)
//                        ->setMaxResults(1)
//                        ->getQuery()
//                        ->getResult();
//    }
}
