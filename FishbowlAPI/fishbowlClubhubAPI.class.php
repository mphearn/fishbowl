<?php
/**
 * @package : FishbowlPI
 * @author : dnewsom <dave.newsom@fishbowlinventory.com>
 * @author : kbatchelor <kevin.batchelor@fishbowlinventory.com>
 * @version : 1.2
 * @date : 2010-04-29
 *
 * Utility routines for Fishbowls API
 */

require_once 'fishbowlAPI.class.php';

class FishbowlClubhubAPI extends FishbowlAPI {
    /**
     * Post sales order information
     * @param string $order - The order information to be sent to Fishbowl.
     */
    public function saveSalesOrder($order) {
        // Setup XML
        $xml = "<SOSaveRq>\n";
        $xml .= $order->toXML();
        $xml .= "</SOSaveRq>\n";

        // strip out unicode characters because fishbowl will error on trying to parse them
        $xml = $this->stripUnicodeCharacters($xml);

        echo "Uploading order " . $order->number . " to Fishbowl...\n";
        // Create request and pack
        $this->createRequest($xml);

        //print_r($this->xmlRequest);

        $len = strlen($this->xmlRequest);
        $packed = pack("N", $len);

        // Send and get the response
        fwrite($this->id, $packed, 4);
        fwrite($this->id, $this->xmlRequest);
        $this->getResponse();

        //print_r($this->xmlResponse);

        // Set the result
        $this->setResult($this->parseXML($this->xmlResponse));
        $this->setStatus($status);

        echo "Update status = " . $status . "\n";
    }

    public function saveSalesOrderXml($xml) {
        $xml = stripUnicodeCharacters($xml);

        // Create request and pack
        $this->createRequest($xml);

        $len = strlen($this->xmlRequest);
        $packed = pack("N", $len);

        // Send and get the response
        fwrite($this->id, $packed, 4);
        fwrite($this->id, $this->xmlRequest);
        $this->getResponse();

        // Set the result
        $this->setResult($this->parseXML($this->xmlResponse));
        $this->setStatus($status);
    }

    private function stripUnicodeCharacters($string) {
        return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x80-\x9F]/u', '', $string);
    }
}

?>
