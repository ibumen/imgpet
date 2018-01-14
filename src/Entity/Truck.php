<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TruckRepository")
 * @UniqueEntity(fields="truckNumber", message="Truck Number already taken.")
 */
class Truck {
    use \App\Utility\Utils;
    
    public function __construct() {
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
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="App\Entity\TruckOwner", inversedBy="trucks")
     * @ORM\JoinColumn(name="truckowner_id", referencedColumnName="truckowner_id", nullable=false)
     */
    private $truckOwner;
    
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

    public function getTruckOwner() {
        return $this->truckOwner;
    }

    public function setTruckOwner($truckowner) {
        $this->truckOwner = $truckowner;
    }
    
    public function getTruckStatus(){
        return $this->truckStatus;
    }
    
    public function setTruckStatus($truckstatus){
        $truckstatus = trim(strtolower($truckstatus));
        if(!in_array($truckstatus, Truck::getUtilTruckStatus())){
            throw new \InvalidArgumentException();
        }
        $this->truckStatus= $truckstatus;
    }

}
