<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FuelStationRepository")
 * @UniqueEntity(fields="stationName", message="Name of fuel station must be unique")
 */
class FuelStation {

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="fuelstation_id", type="integer")
     */
    private $id;

    // add your own fields

    /**
     *
     * @Assert\NotBlank()
     * @ORM\Column(type="string", nullable=false, unique=true, length=30)
     */
    private $stationName;

    //functions

    public function getId() {
        return $this->id;
    }

    public function getStationName() {
        return $this->stationName;
    }

    public function setStationName($pname) {
        $this->stationName = trim($pname);
    }

}