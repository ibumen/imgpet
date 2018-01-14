<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OrgRepository")
 */
class Org {

    /**
     * @ORM\Id
     * @ORM\Column(name= "org_id", type="integer")
     * @ORM\GeneratedValue
     */
    private $id;
    
    /**
     *
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    private $name;

    /**
     *
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=200, nullable=false)
     */
    private $address;

    /**
     *
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=20, nullable=false)
     */
    private $mobileno;
    
    public function getId(){
        return $this->id;
    }
    
    public function getName(){
        return $this->name;
    }
    
    public function getAddress(){
        return $this->address;
    }
    
    public function getMobileno(){
        return $this->mobileno;
    }

}
