<?php

require_once 'FishbowlAPI/models/fbPersonAddress.class.php';
require_once 'FishbowlAPI/models/fbSalesOrderItem.class.php';

/**
 * @author : Sam Bloomquist <sambloomquist@gmail.com>
 * @date : 2014-08-01
 *
 * A very simple class to represent the Fishbowl API's data model for Sale Orders.
 */

class FbSalesOrder {
    public $note;
    public $totalPrice = 0;
    public $totalTax = 0;
    public $itemTotal = 0;
    public $salesman;
    public $number;
    public $status;
    public $carrier;
    public $firstShipDate;
    public $createdDate;
    public $taxRateName;
    public $shippingTerms;
    public $paymentTerms;
    public $customerName;
    public $fob;
    public $quickBooksClassName;
    public $locationGroup;
    public $priorityId;
    public $billTo; // personAddress
    public $shipTo; // personAddress
    public $items = array(); // an array of salesOrderItems

    function __construct() {
      $this->billTo = new FbPersonAddress; // personAddress
      $this->shipTo = new FbPersonAddress; // personAddress
    }

    /**
     * @return XML string of this object's representation in the fishbowlAPI.
     */
    public function toXML() {
      $_xmlString = "<SalesOrder>\n" .
                    "  <Note>" . $this->note . "</Note>\n" .
                    "  <Salesman>" . $this->salesman . "</Salesman>\n" .
                    "  <Number>" . $this->number . "</Number>\n" .
                    "  <Status>" . $this->status . "</Status>\n" .
                    "  <Carrier>" . $this->carrier . "</Carrier>\n" .
                    "  <FirstShipDate>" . $this->firstShipDate . "</FirstShipDate>\n" .
                    "  <CreatedDate>" . $this->createdDate . "</CreatedDate>\n" .
                    "  <TaxRateName>" . $this->taxRateName . "</TaxRateName>\n" .
                    "  <ShippingTerms>" . $this->shippingTerms . "</ShippingTerms>\n" .
                    "  <PaymentTerms>" . $this->paymentTerms . "</PaymentTerms>\n" .
                    "  <CustomerName>" . $this->customerName . "</CustomerName>\n" .
                    "  <FOB>" . $this->fob . "</FOB>\n" .
                    "  <QuickBooksClassName>" . $this->quickBooksClassName . "</QuickBooksClassName>\n" .
                    "  <LocationGroup>" . $this->locationGroup . "</LocationGroup>\n" .
                    "  <PriorityId>" . $this->priorityId . "</PriorityId>\n" .
                    "  <BillTo>\n";
      $_xmlString .= $this->billTo->toXML();
      $_xmlString .="  </BillTo>\n" .
                    "  <Ship>\n";
      $_xmlString .= $this->shipTo->toXML();
      $_xmlString .="  </Ship>\n" .
                    "  <Items>\n";

      foreach ($this->items as $item) {
        $_xmlString .= $item->toXML();
      }

      $_xmlString .= "  </Items>\n" .
                    "</SalesOrder>\n";

      return $_xmlString;
    }

    public function validateSerialNumbers() {
      $valid = true;
      foreach($this->items as $item) {
        if ($item->type == "_assembly") {
          if (empty($item->serialNumbers)) {
            // assembly types are club sets, and they must have serial numbers to be valid
            echo "Order " . $this->number . " has an assembly item with no serial number. Not valid for upload to Fishbowl.\n";
            $valid = false;
            break;
          } else {
            $nums = explode("\n", trim($item->serialNumbers));

            if (count($nums) != $item->quantity) {
              echo "Order " . $this->number . " has a mismatch in the count of serial numbers vs. assembly item qty.  Not valid for upload to Fishbowl.\n";
              $valid = false;
              break;
            } else {
              $this->note .= " " . $item->serialNumbers;
            }
          }
        }
      }

      return $valid;
    }
}

?>
