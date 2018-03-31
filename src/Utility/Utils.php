<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Utility;

/**
 * Description of UtilityConstants
 *
 * @author enesi
 */
trait Utils {

    //put your code here
    public static function getUtilGender() {
        return array("male", "female");
    }

    public static function getUtilCustomerTypes() {
        return array("individual", "organisation");
    }

    public static function getUtilTitles() {
        return array("Mr.", "Mrs.", "Mallam", "Mallama", "Hajiya", "Alhaji", "Master", "Dr.", "Prof.", "Mistress", "Miss", "Sir", "Pa", "Madam", "Oga", "Sheikh");
    }
    
    public static function getUtilProductQuantityMetrics(){
        return array("litres", "barrels", "gallons");
    }
    
    public static function getUtilCustomerStatus(){
        return array("active", "dormant");
    }
    
    public static function getUtilOrderStatus(){
        return array("active", "cancelled", "completed");
    }
    
    public static function getUtilOrderDeliveryStatus(){
        return array("not-delivered", "partial-delivery", "delivered");
    }
    
    public static function getUtilPaymentOptions(){
        return array("cash", "bank transfer", "pos", "cheque", "mobile transfer", "atm transfer");
    }
    
    public static function getUtilTruckStatus(){
        return array("active", "inactive");
    }
    
    public static function getUtilUserStatus(){
        return array("enabled", "disabled");
    }
    
    public static function getUtilUserRoles(){
        return array("ROLE_MANAGER_SALES","ROLE_PERSONNEL_SALES", "ROLE_MANAGER_FACILITY", "ROLE_MANAGER_LOADING", "ROLE_PERSONNEL_LOADING");
    }
    
    public static function getUtilLoadingStatus(){
        return array("loading", "on transit", "delivered with dispute", "delivered");
    }

}
