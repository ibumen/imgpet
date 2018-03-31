<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LoadingRepository")
 * @UniqueEntity(fields="lid", message="Loading Id must be unique")
 * @ORM\Table(name="loading")
 */
class Loading {

    use \App\Utility\Utils;

    public function __construct() {
        $this->loadingRecords = new ArrayCollection();
        $dt = new \DateTime();
        $this->dateCreated = $dt;
        $this->loadingDate = $dt;
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="loading_id", type="integer")
     */
    private $id;
    // add your own fields

    /**
     *
     * @ORM\Column(type="string", length=13, nullable=false, unique=true)
     */
    private $lid;

    /**
     *
     * @Assert\NotBlank(message="Unknown date of entry.")
     * @Assert\DateTime(message = "Date created must be a valid date with time.")
     * @Assert\GreaterThanOrEqual(propertyPath="loadingDate", message="Date Created must be a date on or after the actual loading date.")
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $dateRecorded;

    /**
     *
     * @Assert\NotBlank(message="Unknown date of loading.")
     * @Assert\DateTime(message = "Loading date must be a valid date with time.")
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $loadingDate;

    /**
     *
     * @Assert\NotBlank(message="Select loading depot.")
     * @ORM\ManyToOne(targetEntity="App\Entity\LoadingStation")
     * @ORM\JoinColumn(name="loadingstation_id", referencedColumnName="loadingstation_id", nullable=true)
     */
    private $loadingDepot;


    /**
     *
     * @Assert\DateTime(message = "Date finished must be a valid date with time.")
     * @ORM\Column(type="datetime", nullable=true)

      private $dateFinished;
     */

    /**
     * 
     * @Assert\NotBlank(message="Select a product")
     * @ORM\ManyToOne(targetEntity="App\Entity\Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="product_id", nullable=false)
     */
    private $product;

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
     * @Assert\NotBlank(message="Specify the purchase price")
     * @Assert\Type(type="numeric", message="Purchase Price must be a numeric value.")
     * @Assert\GreaterThan(value=0, message="Purchase Price must be greater than 0.")
     * @ORM\Column(type="decimal", precision=15, scale=2, nullable=false, options={"unsigned":true})
     */
    private $purchasePrice;

    /**
     * 
     * @Assert\NotBlank(message="Select User")
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="user_id", nullable=false)
     */
    private $createdBy;

    /**
     * 
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="finished_by", referencedColumnName="user_id", nullable=true)

      private $finishedBy;
     */
    /**
     * @Assert\Choice(callback= "getUtilLoadingStatus", message="Invalid Loading Status")
     * @ORM\Column(type="string", nullable=false, length=20, options={"default":"loading"})

      private $loadingStatus;
     */

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\LoadingRecord", mappedBy="loading")
     */
    private $loadingRecords;

    public function getId() {
        return $this->id;
    }

    public function getLid() {
        return $this->lid;
    }

    public function setLid($lid) {
        $this->lid = $lid;
    }

    public function getDateRecorded() {
        return $this->dateRecorded;
    }

    public function setDateRecorded($daterecorded) {
        $this->dateRecorded = $daterecorded;
    }

    public function getLoadingDate() {
        return $this->loadingDate;
    }

    public function setLoadingDate($loadingdate) {
        $this->loadingDate = $loadingdate;
    }

    public function getCreatedBy() {
        return $this->createdBy;
    }

    public function setCreatedBy($sperson) {
        $this->createdBy = $sperson;
    }

    public function getLoadingStatus() {
        if (count($this->loadingRecords)) {
            $status = "delivered";
            $sumloadedquantity=0;
            foreach ($this->loadingRecords as $record) {
                $sumloadedquantity += $record->getQuantity();
                if ($record->getLoadingStatus() == "loading") {
                    return "loading";
                } else {
                    if ($record->getLoadingStatus() == "delivered with dispute") {
                        $status = "delivered with dispute";
                    }
                }
            }
            if($sumloadedquantity < $this->getQuantity()){
                $status = "loading";
            }
            return $status;
        }
        return "loading";
    }

    /* public function setLoadingStatus($ostatus) {
      if (!in_array($ostatus, Order::getUtilLoadingStatus())) {
      throw new \InvalidArgumentException("Invalid Loading Status");
      }
      $this->loadingStatus = $ostatus;
      } */

    public function getQuantity() {
        return $this->quantity;
    }

    public function setQuantity($qty) {
        $this->quantity = $qty;
    }

    public function getProduct() {
        return $this->product;
    }

    public function setProduct($product) {
        $this->product = $product;
    }

    public function getPurchasePrice() {
        return $this->purchasePrice;
    }

    public function setPurchasePrice($uprice) {
        $this->purchasePrice = $uprice;
    }

    public function getLoadingDepot() {
        return $this->loadingDepot;
    }

    public function setLoadingDepot($depot) {
        $this->loadingDepot = $depot;
    }

    public function getLoadingRecords(array $status = array()) {
        if (count($status)) {
            $lr = array();
            foreach ($this->loadingRecords as $loadingrecord) {
                if (in_array($loadingrecord->getLoadingStatus(), $status)) {
                    $lr[] = $loadingrecord;
                }
            }
            return $lr;
        }
        return $this->loadingRecords;
    }

    public function setLoadingRecords($record) {
        if (!$this->loadingRecords->contains($record)) {
            $this->loadingRecords[] = $record;
            $record->setLoading($this);
        }
    }

}
