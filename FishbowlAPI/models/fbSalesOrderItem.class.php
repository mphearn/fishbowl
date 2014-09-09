<?php
/**
 * @author : Sam Bloomquist <sambloomquist@gmail.com>
 * @date : 2014-08-01
 *
 * A very simple class to represent the Fishbowl API's data model for Sale Order Items.
 */

class FbSalesOrderItem {
    public $productNumber;
    public $soid;
    public $type;
    public $quantity;
    public $taxable;
    public $productPrice;
    public $uomCode;
    public $itemTypeId;
    public $quickBooksClassName;
    public $kitItemFlag;
    public $showItemFlag;
    public $dateScheduledFulfillment;
    public $serialNumbers;

    /**
     * @return XML string of this object's representation in the fishbowlAPI.
     */
    public function toXML() {
        $_xmlString = "<SalesOrderItem>\n" .
                    "  <ProductNumber>" . $this->productNumber . "</ProductNumber>\n" .
                    "  <SOID>" . $this->soid . "</SOID>\n" .
                    "  <Taxable>" . $this->taxable . "</Taxable>\n" .
                    "  <Quantity>" . $this->quantity . "</Quantity>\n" .
                    "  <ProductPrice>" . $this->productPrice . "</ProductPrice>\n" .
                    "  <UOMCode>" . $this->uomCode . "</UOMCode>\n" .
                    "  <ItemType>" . $this->itemTypeId . "</ItemType>\n" .
                    "  <QuickBooksClassName>" . $this->quickBooksClassName . "</QuickBooksClassName>\n" .
                    "  <KitItemFlag>" . $this->kitItemFlag . "</KitItemFlag>\n" .
                    "  <ShowItemFlag>" . $this->showItemFlag . "</ShowItemFlag>\n" .
                    "  <DateScheduledFulfillment>" . $this->dateScheduledFulfillment . "</DateScheduledFulfillment>\n" .
                    "</SalesOrderItem>\n";

        return $_xmlString;
    }
}

?>
