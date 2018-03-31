<?php

namespace App\Utility;

use Symfony\Component\Validator\Constraints as Assert;

class LoadingFind {

    /**
     * @Assert\NotBlank(message="Loading Id is Required")
     */
    private $lid;

    // add your own fields

    public function getLid() {
        return $this->lid;
    }
    
    public function setLid($lid) {
        $this->lid=$lid;
    }   
}
