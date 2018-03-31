<?php

namespace App\Utility;

use Symfony\Component\Validator\Constraints as Assert;

class LoadingRecordFind {

    /**
     * @Assert\NotBlank(message="Loading Record Id is Required")
     */
    private $lrid;

    // add your own fields

    public function getLrid() {
        return $this->lrid;
    }
    
    public function setLrid($lrid) {
        $this->lrid=$lrid;
    }   
}
