<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ExpenseRepository")
 */
class Expense {

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

    // add your own fields

    /**
     *
     * @Assert\NotBlank(message="Date of truck expense must be specified.")
     * @Assert\DateTime(message = "Date of truck expense must be a valid date with time.")
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $dateOfExpense;

    /**
     *
     * @Assert\NotBlank(message="Unknown date of entry.")
     * @Assert\DateTime(message = "Date recorded must be a valid date with time.")
     * @Assert\GreaterThanOrEqual(propertyPath="dateOfExpense", message="Date Recorded must be a date on or after the actual date of truck expense.")
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $dateRecorded;

    /**
     *
     * @Assert\NotBlank(message="Invalid amount.")
     * @Assert\Type(type="numeric", message="Amount must be a numeric value.")
     * @Assert\GreaterThan(value=0, message="Amount must be greater than 0.")
     * @ORM\Column(type="decimal", precision=15, scale=2, nullable=false)
     */
    private $amount;

    /**
     * @Assert\NotBlank(message="Invalid reason.")
     * @ORM\Column(type="string", nullable=false, length=500)
     */
    private $reason;

    /**
     * 
     * @Assert\NotBlank(message="Invalid user")
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="entered_by", referencedColumnName="user_id", nullable=false)
     */
    private $enteredBy;

    public function getId() {
        return $this->id;
    }

    public function getDateOfExpense() {
        return $this->dateOfExpense;
    }

    public function setDateOfExpense($date) {
        $this->dateOfExpense = $date;
    }

    public function getDateRecorded() {
        return $this->dateRecorded;
    }

    public function setDateRecorded($date) {
        $this->dateRecorded = $date;
    }

    public function getAmount() {
        return $this->amount;
    }

    public function setAmount($amt) {
        $this->amount = $amt;
    }

    public function getReason() {
        return $this->reason;
    }

    public function setReason($reason) {
        $this->reason = trim($reason);
    }

    public function getEnteredBy() {
        return $this->enteredBy;
    }

    public function setEnteredBy($user) {
        $this->enteredBy = $user;
    }

}
