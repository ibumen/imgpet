<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * xxx@ORM\Entity(repositoryClass="App\Repository\TruckOwnerRepository")
 */
class TruckOwner {

    use \App\Utility\Utils;
    use \App\Utility\NameUtils;

    public function __construct() {
        $this->trucks = new ArrayCollection();
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="truckowner_id", type="integer")
     */
    private $id;

    // add your own fields

    /**
     *
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=20, nullable=false)
     */
    private $fname;

    /**
     *
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=20, nullable=false)
     */
    private $lname;

    /**
     *
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $oname;


    /**
     *
     * @Assert\Choice(callback="getUtilTitles", message="Invalid Title.")
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $title;

    public function getId() {
        return $this->id;
    }

    public function getFname() {
        return $this->fname;
    }

    public function setFname($fname) {
        $this->fname = $fname;
    }

    public function getLname() {
        return $this->lname;
    }

    public function setLname($lname) {
        $this->lname = $lname;
    }

    public function getOname() {
        return $this->oname;
    }

    public function setOname($oname) {
        $this->oname = $oname;
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($title) {
        $title = trim($title);
        if (!in_array($title, TruckOwner::getUtilTitles())) {
            throw new \InvalidArgumentException("Invalid Title");
        }
        $this->title = $title;
    }



}
