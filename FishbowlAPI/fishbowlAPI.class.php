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

class FishbowlAPI {
    public $result;
    public $statusCode;
    public $statusMsg;
    public $loggedIn;
    public $userRights;
    protected $xmlRequest;
    protected $xmlResponse;
    protected $id;
    protected $key;
    protected $fbErrorCodes;

    /**
     * Create the connection to Fishbowl
     * @param string $host - Fishbowl host
     * @param string $port - Fishbowl port
     */
    public function __construct($host, $port) {
        $this->host = $host;
        $this->port = $port;

        $this->id = fsockopen($this->host, $this->port);
        $this->fbErrorCodes = new FBErrorCodes();
    }

    /**
     * Close the connection
     */
    public function closeConnection() {
        fclose($this->id);
    }

    /**
     * Login to Fishbowl
     * @param string $user - Pass in the username on login
     * @param string $pass - Pass in the password on login
     */
    public function login($user = null, $pass = null) {
        if (!is_null($user)) {
            $this->user = $user;
        }
        if (!is_null($pass)) {
            $this->pass = base64_encode(md5($pass, true));
        }
        // Parse XML
        $this->xmlRequest = "<FbiXml>\n".
                            "    <Ticket/>\n" .
                            "    <FbiMsgsRq>\n" .
                            "        <LoginRq>\n" .
                            "            <IAID>" . APP_KEY . "</IAID>\n" .
                            "            <IAName>" . APP_NAME . "</IAName>\n" .
                            "            <IADescription>" . APP_DESCRIPTION . "</IADescription>\n" .
                            "            <UserName>" . $this->user . "</UserName>\n" .
                            "            <UserPassword>" . $this->pass . "</UserPassword>\n" .
                            "        </LoginRq>\n" .
                            "    </FbiMsgsRq>\n" .
                            "</FbiXml>";

        // Pack for sending
        $len = strlen($this->xmlRequest);
        $packed = pack("N", $len);

        // Send and get the response
        fwrite($this->id, $packed, 4);
        fwrite($this->id, $this->xmlRequest);
        $this->getResponse();

        // Set the result
        $this->setResult($this->parseXML($this->xmlResponse));
        $this->setStatus('LoginRs');

        if ($this->statusCode == 1000) {
            // Set the key
            $this->key = $this->result['Ticket']['Key'];
            $this->loggedIn = true;
            $this->userRights = $this->result['FbiMsgsRs']['LoginRs']['ModuleAccess']['Module'];
        } else {
            $this->loggedIn = false;
        }
    }

    /**
     * Get customer information
     * @param string $type - What type of call are you running. Default is NameList
     * @param string $name - If your getting a specific customer you must pass in a name
     */
    public function getCustomer($type = 'NameList', $name = null) {
        // Setup XML
        if ($type == "Get") {
            $xml = "<CustomerGetRq>\n<Name>{$name}</Name>\n</CustomerGetRq>\n";
            $status = 'CustomerGetRs';
        } elseif ($type == "List") {
            $xml = "<CustomerListRq></CustomerListRq>\n";
            $status = 'CustomerListRs';
        } else {
            $xml = "<CustomerNameListRq></CustomerNameListRq>\n";
            $status = 'CustomerNameListRs';
        }

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

    /**
     * Get vendor information
     * @param string $type - What type of call are you running. Default is NameList
     * @param string $name - If your getting a specific vendor you must pass in a name
     */
    function getVendor($type = 'NameList', $name = null) {
        if ($type == "Get") {
            $xml = "<VendorGetRq>\n<Name>{$name}</Name>\n</VendorGetRq>\n";
            $status = "VendorGetRs";
        } elseif ($type == "List") {
            $xml = "<VendorListRq></VendorListRq>\n";
            $status = "VendorListRs";
        } else {
            $xml = "<VendorNameListRq></VendorNameListRq>\n";
            $status = "VendorNameListRs";
        }

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

    /**
     * Get product information
     * @param string $type
     * @param string $productNum
     * @param integer $getImage
     * @param string $upc
     */
    public function getProducts($type = 'Get', $productNum = 'B201', $getImage = 0, $upc = null) {
        // Setup XML
        if ($type == "Get") {
            $xml = "<ProductGetRq>\n" .
                   "    <Number>{$productNum}</Number>\n" .
                   "    <GetImage>{$getImage}</GetImage>\n" .
                   "</ProductGetRq>\n";
        } elseif ($type == "Query") {
            $xml = "<ProductQueryRq>\n";
                if ($upc != null) {
                    $xml .= "    <UPC>{$upc}</UPC>\n";
                } else {
                    $xml .= "    <ProductNum>{$productNum}</ProductNum>\n";
                }
            $xml .= "    <GetImage>{$getImage}</GetImage>\n" .
                    "</ProductQueryRq>\n";
        }

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
        $this->setStatus('ProductQueryRs');
    }

    /**
     * Get list of SO's by location group
     * @param string $LocationGroup
     */
    public function getSOList($LocationGroup = 'SLC') {
        // Parse XML
        $xml = "<GetSOListRq>\n<LocationGroup>{$LocationGroup}</LocationGroup>\n</GetSOListRq>\n";

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
        $this->setStatus('GetSOListRs');
    }

    /**
     * Loads SO for a given number
     * @param string $number
     */
    public function getSO($number = '50032') {
        // Parse XML
        $xml = "<LoadSORq>\n<Number>{$number}</Number>\n</LoadSORq>\n";

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
        $this->setStatus('LoadSORs');
    }

    /**
     * Get part information. Can be search by either PartNum or UPC
     * @param string $partNum - Pass in if you're searching for PartNum or pass in null
     * @param string $upc - Pass in if you're searching for UPC or pass in null
     */
    public function getPart($partNum = null, $upc = null) {
        // Setup xml
        $xml = "<PartGetRq>\n";
        if (!is_null($partNum)) {
            $xml .= "<Number>{$partNum}</Number>\n";
        } else {
            $xml .= "<Number>{$upc}</Number>\n";
        }
        $xml .= "</PartGetRq>\n";

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
        $this->setStatus('PartGetRs');
    }

    /**
     * Get inventory quantity information for a part
     * $param string $partNum
     */
    public function getInvQty($partNum) {
        // Setup xml
        $xml = "<InvQtyRq>\n<PartNum>{$partNum}</PartNum>\n</InvQtyRq>\n";

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
        $this->setStatus('InvQtyRs');
    }

    /**
     * Parse xml data and store the results
     */
    protected function parseXML($xml, $recursive = false, $cust = false) {
        if (!$recursive) {
            $array = simplexml_load_string(utf8_encode($xml));
        } else {
            $array = $xml;
        }

        $newArray = array();
        $array = (array) $array;

        foreach ($array as $key=>$value) {
            $value = (array) $value;
            if (isset($value[0])) {
                if (count($value) > 1) {
                    if ($value[0] instanceof SimpleXMLElement) {
                        $newArray[$value[0]->getName()] = (array) $value[0];
                    }

                    $newArray[$key] = (array) $value;
                } else {
                    $newArray[$key] = trim($value[0]);
                }
            } else {
                $newArray[$key] = $this->parseXML($value, true);
            }
        }
        if (!isset($newArray['statusMessage'])) {
            $newArray['statusMessage'] = "null";
        }
        return $newArray;
    }

    /**
     * Set the XML Request
     * @param string $xmlData
     */
    protected function createRequest($xmlData) {
        $this->xmlRequest = $this->xmlHeader() . $xmlData . $this->xmlFooter();
    }

    /**
     * Create XML header
     */
    protected function xmlHeader() {
        $xml = "<FbiXml>\n<Ticket>\n<UserID>1</UserID>\n<Key>{$this->key}</Key>\n</Ticket>\n<FbiMsgsRq>\n";
        return $xml;
    }

    /**
     * Create XML foorter
     */
    protected function xmlFooter() {
        $xml = "</FbiMsgsRq>\n</FbiXml>\n";
        return $xml;
    }

    /**
     * Determine the length (in bytes) of our reponse and stream it.
     */
    protected function getResponse() {
        $packed_len = stream_get_contents($this->id, 4); //The first 4 bytes contain our N-packed length
        $hdr = unpack('Nlen', $packed_len);
        $len = $hdr['len'];
        $this->xmlResponse = stream_get_contents($this->id, $len);
    }

    /**
     * Set the results from a response
     * @param array $res - This should be the parsed response from the server
     */
    protected function setResult($res) {
        $this->result = $res;
    }

    /**
     * Set the status code and message for the responses
     * @param string $response - This should be the response name to get the code and message from
     */
    protected function setStatus($response) {
        if (isset($this->result[$response])) {
            $this->statusCode = $this->result[$response]['@attributes']['statusCode'];
            $this->statusMsg = $this->result[$response]['@attributes']['statusMessage'];
        } elseif (isset($this->result['FbiMsgsRs'][$response])) {
            $this->statusCode = $this->result['FbiMsgsRs'][$response]['@attributes']['statusCode'];
            $this->statusMsg = $this->result['FbiMsgsRs'][$response]['@attributes']['statusMessage'];
        } else {
            $this->statusCode = $this->result['FbiMsgsRs']['@attributes']['statusCode'];
            $this->statusMsg = $this->result['FbiMsgsRs']['@attributes']['statusMessage'];
        }

        if ($this->statusCode == 1000) {
            $this->statusMsg = 'Success';
        }
    }

    /**
     * Generate the request to send to Fishbowl from an object
     * @param string $name
     * @param array $array
     */
    protected function generateRequest($array, $name, $subname = null) {
        //star and end the XML document
        $this->xmlRequest = "<{$name}>\n";
        if (!is_null($subname)) {
            $this->xmlRequest .= "\t<{$subname}>\n";
        }
        $this->generateXML($array);
        if (!is_null($subname)) {
            $this->xmlRequest .= "\t</{$subname}>\n";
        }
        $this->xmlRequest .= "</{$name}>";
        return $this->xmlRequest;
    }

    /**
     * Generate XML from an array
     * @param array $array
     */
    protected function generateXML($array) {
        static $Depth = 0;
        $Tabs = "";

        // Check if this is the top value
        if (isset($array->data)) {
            $array = $array->data;
        }

        foreach($array as $key => $value){
            unset($Tabs);

            // We want to have arrays, if we find an object we need to convert it
            if (is_object($value)) {
                $value = (array) $value;
            }

            // Check if the node is an array or object
            if (!is_array($value)) {
                // Add tabs so it's readable
                for ($i=1; $i<=$Depth+1; $i++) {
                    $Tabs .= "\t";
                }
                if (preg_match("/^[0-9]\$/",$key)) {
                    $key = "n{$key}";
                }

                // Add to the XML request
                $this->xmlRequest .= "{$Tabs}<{$key}>{$value}</{$key}>\n";
            } else {
                // Add tabs so it's readable
                $Depth += 1;
                for ($i=1; $i<=$Depth; $i++) {
                    $Tabs .= "\t";
                }

                // Add to the XML request and send it to the next level
                $this->xmlRequest .= "{$Tabs}<{$key}>\n";
                $this->generateXML($value);
                $this->xmlRequest .= "{$Tabs}</{$key}>\n";
                $Depth -= 1;
            }
        }
        return true;
    }

    /**
     * Check if the user has rights to functions
     * @param string $module
     * @param string $right
     */
    public function checkAccessRights($module, $right) {
        // Check if the user is admin
        if ($this->user == 'admin') {
            return true;
        }

        // Check if the user has an rights
        if (!is_array($this->userRights)) {
            return false;
        }

        // Create the access right
        $accessRight = $module . "-" . $right;
        if (in_array($accessRight, $this->userRights)) {
            return true;
        } else {
            return false;
        }
    }
}

?>
