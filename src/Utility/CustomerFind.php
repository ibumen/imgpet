<?php

namespace App\Utility;

use Symfony\Component\Validator\Constraints as Assert;

class CustomerFind {

    /**
     * @Assert\NotBlank(message="Customer Unique Id is Required")
     */
    private $cid;

    // add your own fields

    public function getCid() {
        return $this->cid;
    }
    
    public function setCid($cid) {
        $this->cid=$cid;
    }   
}
