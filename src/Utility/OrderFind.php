<?php

namespace App\Utility;

use Symfony\Component\Validator\Constraints as Assert;

class OrderFind {

    /**
     * @Assert\NotBlank(message="Order Id is Required")
     */
    private $oid;

    // add your own fields

    public function getOid() {
        return $this->oid;
    }
    
    public function setOid($oid) {
        $this->oid=$oid;
    }   
}
