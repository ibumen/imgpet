<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TruckRepository")
 * @UniqueEntity(fields="truckNumber", message="Truck Number already taken.")
 */
class Truck {

    use \App\Utility\Utils;

    public function __construct() {
        $this->truckExpenses = new ArrayCollection();
        $this->setTruckStatus("active");
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="truck_id", type="integer")
     */
    private $id;

    // add your own fields

    /**
     *
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=20, nullable=false, unique=true)
     */
    private $truckNumber;

    /**
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $driverName;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TruckExpense", mappedBy="truck")
     */
    private $truckExpenses;

    /**
     * 
     * @Assert\Choice(callback="getUtilTruckStatus", message="Invalid Truck Status")
     * @ORM\Column(type="string", length=10, nullable=false, options={"default":"inactive"})
     */
    private $truckStatus;

    public function getId() {
        return $this->id;
    }

    public function getTruckNumber() {
        return $this->truckNumber;
    }

    public function setTruckNumber($number) {
        $this->truckNumber = $number;
    }

    /*     * public function getTruckOwner() {
      return $this->truckOwner;
      } */

    /*     * public function setTruckOwner($truckowner) {
      $this->truckOwner = $truckowner;
      } */

    public function getTruckStatus() {
        return $this->truckStatus;
    }

    public function setTruckStatus($truckstatus) {
        $truckstatus = trim(strtolower($truckstatus));
        if (!in_array($truckstatus, Truck::getUtilTruckStatus())) {
            throw new \InvalidArgumentException();
        }
        $this->truckStatus = $truckstatus;
    }

    public function getDriverName() {
        return $this->driverName;
    }

    public function setDriverName($drivername) {
        $this->driverName = $drivername;
    }

    /**
     * @return Collection|TruckExpense[]
     */
    public function getTruckExpenses() {
        return $this->truckExpenses;
    }

    public function addTruckExpenses($expense) {
        if (!$this->truckExpenses->contains($expense)) {
            $this->truckExpenses[] = $expense;
            $expense->setTruck($this);
        }
    }

}
