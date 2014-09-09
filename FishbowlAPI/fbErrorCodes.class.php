<?php
/**
 * @package : FishbowlErrorCodes
 * @author : dnewsom <dave.newsom@fishbowlinventory.com>
 * @version : 1.0
 * @date : 2010-04-29
 *
 * Error codes for Fishbowls SDK
 */

class FBErrorCodes {
    
    public function __construct() {
    }

    public function checkCode($code) {
        switch($code) {
            case "1000":
                $value = "Success!";
                break;
            case "1001":
                $value = "Unknown Message Received";
                break;
            case "1002":
                $value = "Connection to Fishbowl Server was lost";
                break;
            case "1003":
                $value = "Some Requests had errors -- now isn't that helpful...";
                break;
            case "1004":
                $value = "There was an error with the database.";
                break;
            case "1009":
                $value = "Fishbowl Server has been shut down.";
                break;
            case "1010":
                $value = "You have been logged off the server by an administrator.";
                break;
            case "1012":
                $value = "Unknown request function.";
                break;
            case "1100":
                $value = "Unknown login error occurred.";
                break;
            case "1110":
                $value = "A new Integrated Application has been added to Fishbowl Inventory. Please contact your Fishbowl Inventory Administrator to approve this Integrated Application.";
                break;
            case "1111":
                $value = "This Integrated Application registration key does not match.";
                break;
            case "1112":
                $value = "This Integrated Application has not been approved by the Fishbowl Inventory Administrator.";
                break;
            case "1120":
                $value = "Invalid Username or Password.";
                break;
            case "1130":
                $value = "Invalid Ticket passed to Fishbowl Inventory Server.";
                break;
            case "1131":
                $value = "Invalid Key value.";
                break;
            case "1140":
                $value = "Initialization token is not correct type.";
                break;
            case "1150":
                $value = "Request was invalid";
                break;
            case "1160":
                $value = "Response was invalid.";
                break;
            case "1162":
            	$value = "The login limit has been reached for the server's key.";
            	break;
            case "1200":
                $value = "Custom Field is invalid.";
                break;
            case "1500":
                $value = "The import was not properly formed.";
                break;
            case "1501":
                $value = "That import type is not supported";
                break;
            case "1502":
                $value = "File not found.";
                break;
            case "1503":
                $value = "That export type is not supported.";
                break;
            case "1504":
                $value = "File could not be written to.";
                break;
            case "1505":
                $value = "The import data was of the wrong type.";
                break;
            case "2000":
                $value = "Was not able to find the Part {0}.";
                break;
            case "2001":
                $value = "The part was invalid.";
                break;
            case "2100":
                $value = "Was not able to find the Product {0}.";
                break;
            case "2101":
                $value = "The product was invalid.";
                break;
            case "2200":
                $value = "The yield failed.";
                break;
            case "2201":
                $value = "Commit failed.";
                break;
            case "2202":
                $value = "Add initial inventory failed.";
                break;
            case "2203":
                $value = "Can not adjust committed inventory.";
                break;
            case "2300":
                $value = "Was not able to find the Tag number {0}.";
                break;
            case "2301":
                $value = "The tag is invalid.";
                break;
            case "2302":
                $value = "The tag move failed.";
                break;
            case "2303":
                $value = "Was not able to save Tag number {0}.";
                break;
            case "2304":
                $value = "Not enough available inventory in Tagnumber {0}.";
                break;
            case "2305":
                $value = "Tag number {0} is a location.";
                break;
            case "2400":
                $value = "Invalid UOM.";
                break;
            case "2401":
                $value = "UOM {0} not found.";
                break;
            case "2402":
                $value = "Integer UOM {0} cannot have non-integer quantity.";
                break;
            case "2500":
                $value = "The Tracking is not valid.";
                break;
            case "2510":
                $value = "Serial number is missing.";
                break;
            case "2511":
                $value = "Serial number is null.";
                break;
            case "2512":
                $value = "Serial number is duplicate.";
                break;
            case "2513":
                $value = "Serial number is not valid.";
                break;
            case "2600":
                $value = "Location not found.";
                break;
            case "2601":
                $value = "Invalid location.";
                break;
            case "2602":
                $value = "Location Group {0} not found.";
                break;
            case "3000":
                $value = "Customer {0} not found.";
                break;
            case "3001":
                $value = "Customer is invalid.";
                break;
            case "3100":
                $value = "Vendor {0} not found.";
                break;
            case "3101":
                $value = "Vendor is invalid.";
                break;
            case "4000":
                $value = "There was an error load PO {0}.";
                break;
            case "4001":
                $value = "Unknow status {0}.";
                break;
            case "4002":
                $value = "Unknown carrier {0}.";
                break;
            case "4003":
                $value = "Unknown QuickBooks class {0}.";
                break;
            case "4004":
                $value = "PO does not have a PO number. Please turn on the auto-assign PO number option in the purchase order module options.";
                break;
        }

        return $value;
    }
}

?>