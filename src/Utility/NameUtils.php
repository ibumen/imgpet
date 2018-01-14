<?php
namespace App\Utility;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

trait NameUtils {

    public function getFullName($format = "f o l") {
        $result = "";
        $strArr = str_split($format);
        foreach ($strArr as $token) {
            switch ($token) {
                case "f":
                    $result .= ucfirst($this->getFname());
                    break;
                case "F":
                    $result .= strtoupper($this->getFname());
                    break;
                case "o":
                    $result .= ucfirst($this->getOname());
                    break;
                case "O":
                    $result .= strtoupper($this->getOname());
                    break;
                case "l":
                    $result .= ucfirst($this->getLname());
                    break;
                case "L":
                    $result .= strtoupper($this->getLname());
                    break;
                case "T":
                    $result .= strtoupper($this->getTitle());
                    break;
                default:
                    $result .= $token;
                    break;
            }
        }
        return $result;
    }

}
