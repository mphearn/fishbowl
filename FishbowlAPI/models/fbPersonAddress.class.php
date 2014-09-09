<?php
/**
 * @author : Sam Bloomquist <sambloomquist@gmail.com>
 * @date : 2014-08-01
 *
 * A very simple class to represent the Fishbowl API's data model for addresses.
 */

class FbPersonAddress {
    public $name;
    public $addressField;
    public $city;
    public $zip;
    public $country;
    public $state;

    /**
     * @return XML string of this object's representation in the fishbowlAPI.
     */
    public function toXML() {
      $_xmlString = "    <Name>" . $this->name . "</Name>\n" .
                    "    <AddressField>" . $this->addressField . "</AddressField>\n" .
                    "    <City>" . $this->city . "</City>\n" .
                    "    <Zip>" . $this->zip . "</Zip>\n" .
                    "    <Country>" . $this->country . "</Country>\n" .
                    "    <State>" . $this->state . "</State>\n";

      return $_xmlString;
    }
}

?>
