<?php
require_once 'NetSuite/PHPToolkit_2014_1/PHPToolkit/NetSuiteService.php';
require_once 'FishbowlAPI/fbErrorCodes.class.php';
require_once 'FishbowlAPI/fishbowlClubhubAPI.class.php';
require_once 'FishbowlAPI/models/fbSalesOrder.class.php';
require_once 'FBconfig.php';
require_once 'so_helpers.php';

// Fishbowl App Info
define('APP_KEY', $app_key);
define('APP_NAME', $app_name);
define('APP_DESCRIPTION', $app_description);

$service = new NetSuiteService();
$search = new TransactionSearchAdvanced();
$search->savedSearchId = "37"; // HH Sales Export Saved Search
// $search->savedSearchId = "48"; // HH Sales Export Saved Search - for QA

$request = new SearchRequest();
$request->searchRecord = $search;

$searchResponse = $service->search($request);
$int_ids = array();

date_default_timezone_set('America/Los_Angeles');
$today = date("mdYHis");

if (!$searchResponse->searchResult->status->isSuccess) {
    exit("NETSUITE SEARCH ERROR\n");
}

echo "NETSUITE SEARCH SUCCESS, records found: " .
        $searchResponse->searchResult->totalRecords . "\n";

// Exit if there are no new orders.
if(!$searchResponse->searchResult->totalRecords) { exit("No new orders to be shipped.\n"); }

// Create Fishbowl Connection
$fbapi = new FishbowlClubhubAPI("r2.fishbowlhostedservices.com", "32291");
$fbapi->Login($fishbowl_user, $fishbowl_pw);

if ($fbapi->loggedIn) {
    echo "Logged in to Fishbowl successfully.\n";
} else {
    $msg = "An error occurred logging in to Fishbowl: " . $fbapi->statusCode .
            ", " . $fbapi->statusMsg . "\n";
    error_log($msg);
    echo $msg;
    exit($msg);
}
// Get the list of records from the NetSuite search.
$records = $searchResponse->searchResult->searchRowList->searchRow;

$salesOrders = array(); // an array of FishBowl SalesOrder objects

// Keep track of the last order record processed to know whether to start a new order or just process an item.
$lastOrder = null;
foreach ($records as $record) {
    // Get Location Name
    foreach ($record->locationJoin->customFieldList->customField as $customField) {
        if ($customField->internalId=='161') { // Location Code (PRODUCTION)
        // if($customField->internalId=='165'){ // Location Code (QA)
            // echo "Location: " . $customField->searchValue . "\n";
            $location =  $customField->searchValue;
        }
    }
    //echo "Location: " . $location . "\n";

    if ($location == "LASVEGAS") {
        //if (($lastOrder == null) || ($lastOrder->number != $record->basic->tranId[0]->searchValue)) {
        if (($lastOrder == null) || ($lastOrder->number != $record->basic->tranId[0]->searchValue)) {
            // Create a new order and the first item
            $lastOrder = fishbowlOrderFromNetSuiteRecord($record);
            $lastOrder->items[] = fishbowlOrderItemFromNetSuiteRecord($record, $lastOrder);
            $salesOrders[] = $lastOrder;
        } else {
            // Just add this item to the last order
            $lastOrder->items[] = fishbowlOrderItemFromNetSuiteRecord($record, $lastOrder);
        }
    }
}

foreach ($salesOrders as $salesOrder) {
    if ($salesOrder->validateSerialNumbers()) {
        if ($fbapi->saveSalesOrder($salesOrder)) {
            // Add this to the list of internal id's to be marked as uploaded in Netsuite
            array_push($int_ids, $salesOrder->items[0]->soid);
        }
    }
}
// Close the connection to Fishbowl.
$fbapi->closeConnection();

$uniqueIdList = array_unique($int_ids); // Filter out duplicate id's
foreach($uniqueIdList as $soId) {
    markUploaded($soId); // Mark the checkbox in Netsuite to Checked
}

?>
