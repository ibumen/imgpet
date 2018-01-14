<?php

namespace App\Utility;

use Symfony\Component\Validator\Constraints as Assert;

class TransactionFind {

    /**
     * @Assert\NotBlank(message="Transaction Id is Required")
     */
    private $tid;

    // add your own fields

    public function getTid() {
        return $this->tid;
    }
    
    public function setTid($tid) {
        $this->tid=$tid;
    }   
}
