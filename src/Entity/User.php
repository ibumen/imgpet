<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields="username", message="Username already taken")
 */
class User implements UserInterface, \Serializable {

    use \App\Utility\Utils;
    use \App\Utility\NameUtils;

    public function __construct() {
        $this->setDateCreated(new \DateTime());
        $this->setLastLogin(new \DateTime());
        $this->setStatus("enabled");
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="user_id", type="integer")
     */
    private $id;

    // add your own fields

    /**
     * @Assert\NotBlank(message="Username is required.")
     * @ORM\Column(type="string", length=30, nullable=false, unique=true)
     */
    private $username;

    /**
     * 
     * @ORM\Column(name="pwd", type="string", length=60, nullable=false)
     */
    private $password;

    /**
     *
     * @Assert\NotBlank(message="Password is required.")
     * @Assert\Length(min=6, minMessage="password must be at least {{limit}} long")
     */
    private $plainPassword;

    /**
     *
     * @Assert\NotBlank(message="First Name is required.")
     * @ORM\Column(type="string", length=20, nullable=false)
     */
    private $fname;

    /**
     *
     * @Assert\NotBlank(message="Last Name is required.")
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

    /**
     * 
     * @ORM\Column(type="string", length=50, nullable=true, options={"default":"users/default.jpg"})
     */
    private $profilepix;

    /**
     *
     * @Assert\Choice(callback="getUtilUserStatus", message = "Invalid User Status")
     * @ORM\Column(type="string", length=15, nullable=false, options={"default":"enabled"})
     */
    private $status;

    /**
     * @Assert\NotBlank(message="Date created is required.")
     * @Assert\Date(message = "Date Created field must be a valid date.")
     * @ORM\Column(type="date", nullable=false)
     */
    private $dateCreated;

    /**
     * @Assert\NotBlank(message="Date of Last Login is required.")
     * @Assert\DateTime(message = "Last Login field must be a valid date.")
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $lastLogin;

    /**
     *
     * @Assert\NotBlank(message="Role has not been assigned.")
     * @Assert\Choice(callback= "getUtilUserRoles", message="Invalid role assigned")
     * @ORM\Column(type="string", length=30, nullable=false)
     */
    private $roles;

    public function getId() {
        return $this->id;
    }

    public function getUsername() {
        return $this->username;
    }

    public function setUsername($usn) {
        $this->username = $usn;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($pwd) {
        $this->password = $pwd;
    }

    public function getFname() {
        return $this->fname;
    }

    public function setFname($fname) {
        $this->fname = trim($fname);
    }

    public function getLname() {
        return $this->lname;
    }

    public function setLname($lname) {
        $this->lname = trim($lname);
    }

    public function getOname() {
        return $this->oname;
    }

    public function setOname($oname) {
        $this->oname = trim($oname);
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($title) {
        $title = trim($title);
        if (!in_array($title, User::getUtilTitles())) {
            throw new \InvalidArgumentException("Invalid Title");
        }
        $this->title = $title;
    }

    public function getProfilepix() {
        return $this->profilepix;
    }

    public function setProfilepix($pix) {
        $this->profilepix = trim($pix);
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $status = trim(strtolower($status));
        if (!in_array($status, User::getUtilUserStatus())) {
            throw new \InvalidArgumentException("Invalid Status");
        }
        $this->status = $status;
    }

    public function getDateCreated() {
        return $this->dateCreated;
    }

    public function setDateCreated($datecreated) {
        $this->dateCreated = $datecreated;
    }

    public function getLastLogin() {
        return $this->lastLogin;
    }

    public function setLastLogin($lastLogin) {
        $this->lastLogin = $lastLogin;
    }

    public function getPlainPassword() {
        return $this->plainPassword;
    }

    public function setPlainPassword($pwd) {
        $this->plainPassword = $pwd;
    }

    public function getSalt() {
        return null;
    }

    public function getRoles() {
        $r = array();
        $roles = array();
        $roles = explode(',', $this->roles);
        foreach ($roles as $role) {
            switch ($role) {
                case 'ROLE_ADMIN':
                    $r[] = 'ROLE_ADMIN';
                case 'ROLE_MANAGER_SALES':
                    $r[] = 'ROLE_MANAGER_SALES';
                case 'ROLE_PERSONNEL_SALES':
                    $r[] = 'ROLE_PERSONNEL_SALES';
            }
            switch ($role) {
                case 'ROLE_ADMIN':
                    $r[] = 'ROLE_ADMIN';
                case 'ROLE_MANAGER_FACILITY';
                    $r[] = 'ROLE_MANAGER_FACILITY';
            }
            
            switch ($role) {
                case 'ROLE_ADMIN':
                    $r[] = 'ROLE_ADMIN';
                case 'ROLE_MANAGER_LOADING';
                    $r[] = 'ROLE_MANAGER_LOADING';
                case 'ROLE_PERSONNEL_LOADING';
                    $r[] = 'ROLE_PERSONNEL_LOADING';
            }
        }
        return array_unique($r);
    }

    public function setRoles($roles) {
        if (is_array($roles)) {
            $this->roles = implode(',', $roles);
        } else {
            $this->roles = $roles;
        }
    }

    public function eraseCredentials() {
        ;
    }

    public function serialize() {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            $this->fname,
            $this->lname,
            $this->oname,
            $this->title,
            $this->profilepix,
            $this->dateCreated,
            $this->lastLogin,
            $this->status,
            $this->roles,
// see section on salt below
// $this->salt,
        ));
    }

    public function unserialize($serialized) {
        list (
                $this->id,
                $this->username,
                $this->password,
                $this->fname,
                $this->lname,
                $this->oname,
                $this->title,
                $this->profilepix,
                $this->dateCreated,
                $this->lastLogin,
                $this->status,
                $this->roles
// see section on salt below
// $this->salt
                ) = unserialize($serialized);
    }

}
