<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LoadingRecordRepository")
 * @UniqueEntity(fields="lrid", message="Loading Record Id must be unique")
 * @ORM\Table(name="loadingrecord")
 */
class LoadingRecord {

    use \App\Utility\Utils;

    public function __construct() {
        $dt = new \DateTime();
        $this->dateRecorded = $dt;
        $this->loadingStatus = "loading";
        $this->logisticsPaid = 0;
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="loading_record_id", type="integer")
     */
    private $id;
    // add your own fields

    /**
     *
     * @ORM\Column(type="string", length=14, nullable=false, unique=true)
     */
    private $lrid;

    /**
     *
     * @Assert\NotBlank(message="Unknown date of entry.")
     * @Assert\DateTime(message = "Date of entry must be a valid date with time.")
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $dateRecorded;

    /**
     *
     * @Assert\DateTime(message = "Delivery date must be a valid date with time.")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deliveryDate;

    /**
     *
     * @Assert\NotBlank(message="Specify truck number.")
     * @ORM\Column(type="string", length=20, nullable=false)
     */
    private $truckNo;

    /**
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $driverName;

    /**
     *
     * @Assert\NotBlank(message="Specify meter ticket no.")
     * @ORM\Column(type="string", length=20, nullable=false)
     */
    private $meterTicket;

    /**
     *
     * @Assert\NotBlank(message="Please select loading entry")
     * @ORM\ManyToOne(targetEntity="App\Entity\Loading", inversedBy="loadingRecords")
     * @ORM\JoinColumn(name="loading_id", referencedColumnName="loading_id", nullable=false)
     */
    private $loading;

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
     * @Assert\Type(type="numeric", message="Quantity delivered must be a numeric value.")
     * @Assert\LessThanOrEqual(propertyPath="quantity", message="Quantity delivered must not be greater than quantity loaded.")
     * @Assert\GreaterThanOrEqual(value=0, message="Quantity must be greater than or equal to 0.")
     * @ORM\Column(type="integer", nullable=true, options={"unsigned":true, "default":0})
     */
    private $deliveredQuantity;

    /**
     *
     * @Assert\NotBlank(message="Specify the cost of logistics")
     * @Assert\Type(type="numeric", message="Cost of logistics must be a numeric value.")
     * @Assert\GreaterThan(value=0, message="Cost of logistics must be greater than 0.")
     * @ORM\Column(type="decimal", precision=15, scale=2, nullable=false, options={"unsigned":true})
     */
    private $costOfLogistics;

    /**
     *
     * @Assert\Type(type="numeric", message="Payment for logistics must be a numeric value.")
     * @Assert\GreaterThanOrEqual(value=0, message="Payment for logistics must be greater than 0.")
     * @Assert\LessThanOrEqual(propertyPath="costOfLogistics", message="Payment for logistics must not be greater than cost of logistics.")
     * @ORM\Column(type="decimal", precision=15, scale=2, nullable=true, options={"unsigned":true, "default":0})
     */
    private $logisticsPaid;

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
     */
    private $finishedBy;

    /**
     * @Assert\Choice(callback= "getUtilLoadingStatus", message="Invalid Loading Status")
     * @ORM\Column(type="string", nullable=false, length=50, options={"default":"loading"})
     */
    private $loadingStatus;

    /**
     *
     * @ORM\Column(type="string", nullable=true, length=300)
     */
    private $finishingRemark;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProductDistribution", mappedBy="loadingRecord")
     */
    private $productDistributions;

    public function getId() {
        return $this->id;
    }

    public function getLrid() {
        return $this->lrid;
    }

    public function setLrid($lrid) {
        $this->lrid = $lrid;
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

    public function getCreatedBy() {
        return $this->createdBy;
    }

    public function setCreatedBy($sperson) {
        $this->createdBy = $sperson;
    }

    public function getFinishedBy() {
        return $this->finishedBy;
    }

    public function setFinishedBy($sperson) {
        $this->finishedBy = $sperson;
    }

    public function getLoadingStatus() {
        return $this->loadingStatus;
    }

    public function setLoadingStatus($ostatus) {
        if (!in_array($ostatus, Order::getUtilLoadingStatus())) {
            throw new \InvalidArgumentException("Invalid Loading Status");
        }
        $this->loadingStatus = $ostatus;
    }

    public function getQuantity() {
        return $this->quantity;
    }

    public function setQuantity($qty) {
        $this->quantity = $qty;
    }

    public function getDeliveredQuantity() {
        return $this->deliveredQuantity;
    }

    public function setDeliveredQuantity($qty) {
        $this->deliveredQuantity = $qty;
        if ($this->deliveredQuantity == $this->quantity && $this->costOfLogistics == $this->logisticsPaid) {
            $this->loadingStatus = "delivered";
        } else {
            if ($this->deliveredQuantity > 0) {
                $this->loadingStatus = "delivered with dispute";
            }
        }
    }

    public function getLoading() {
        return $this->loading;
    }

    public function setLoading($loading) {
        $this->loading = $loading;
    }

    public function getCostOfLogistics() {
        return $this->costOfLogistics;
    }

    public function setCostOfLogistics($cost) {
        $this->costOfLogistics = $cost;
    }

    public function getLogisticsPaid() {
        return $this->logisticsPaid;
    }

    public function setLogisticsPaid($advance) {
        $this->logisticsPaid = $advance;
        if ($this->deliveredQuantity == $this->quantity && $this->costOfLogistics == $this->logisticsPaid) {
            $this->loadingStatus = "delivered";
        } else {
            if ($this->deliveredQuantity > 0) {
                $this->loadingStatus = "delivered with dispute";
            }
        }
    }

    public function getDriverName() {
        return $this->driverName;
    }

    public function setDriverName($drivername) {
        $this->driverName = $drivername;
    }

    public function getTruckNo() {
        return $this->truckNo;
    }

    public function setTruckNo($truckno) {
        $this->truckNo = $truckno;
    }

    public function getMeterTicket() {
        return $this->meterTicket;
    }

    public function setMeterTicket($mtk) {
        $this->meterTicket = $mtk;
    }

    public function getFinishingRemark() {
        return $this->finishingRemark;
    }

    public function setFinishingRemark($rmk) {
        $this->finishingRemark = $rmk;
    }

    public function getProductDistributions() {
        return $this->productDistributions;
    }

    public function setProductDistributions($dist) {
        if (!$this->productDistributions->contains($dist)) {
            $this->productDistributions[] = $dist;
            $dist->setLoadingRecord($this);
        }
    }
    
    public function getDistributedQuantity(){
        $totalquantitydistributed=0;
        foreach($this->productDistributions as $dist){
            $totalquantitydistributed += $dist->getQuantityDelivered();
        }
        return $totalquantitydistributed;
    }

}
