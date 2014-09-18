<?php

function fixDate($inDate) {
    // Convert date into string, adjust for time zone,  and then correct date format
    $tzNewYork = new DateTimeZone("America/New_York");
    $tzPhoenix = new DateTimeZone("America/Phoenix");

    // First, do a timezone adjustment to make sure we have the right calendar date.
    $d = new DateTime($inDate,$tzPhoenix);
    $d->setTimezone($tzNewYork);
    // The Fishbowl API expects a full timestamp, so just set the hours, mins, and seconds
    //      to zeros.
    return $d->format('Y-m-d\T') . "00:00:00";
}

function shipService($shipId) {
    switch ($shipId) {
        case 122:
            $shipVia = "FedexGround";
            break;
        case 127:
            $shipVia = "FedExExpressSaver";
            break;
        case 120:
            $shipVia = "FedExFirstOvernight";
            break;
        case 140:
            $shipVia = "FedExFirstOvernightSat";
            break;
        case 126:
            $shipVia = "Fedex2Day";
            break;
        case 130:
            $shipVia = "Fedex2DayAM";
            break;
        case 145:
            $shipVia = "Fedex2DayAMSat";
            break;
        case 141:
            $shipVia = "Fedex2DaySat";
            break;
        case 128:
            $shipVia = "FedexPriorityOvernight";
            break;
        case 144:
            $shipVia = "FedexPriorityOvernightSat";
            break;
        case 129:
            $shipVia = "FedexStandardOvernight";
            break;
        default:
            $shipVia = "UNKNOWN";
    }
    return $shipVia;
}

function markUploaded($soid) {
    $service2 = new NetSuiteService();
    $gr = new GetRequest();
    $gr->baseRef = new RecordRef();
    // $gr->baseRef->internalId = $addResponse->writeResponse->baseRef->internalId;
    // $gr->baseRef->internalId = $soid;

    $gr->baseRef->internalId = $soid;
    echo "SO ID: " . $soid . "\n";
    $gr->baseRef->type = "salesOrder";

    $getResponse = $service2->get($gr);
    if (!$getResponse->readResponse->status->isSuccess) {
            $msg = "GET ERROR on SO Internal Id lookup\n";
            error_log($msg);
            echo $msg;
            exit();
    } else {
            echo "GET SUCCESS\n";
    }

    echo "\n-----------------------\n";

    $so2 = $getResponse->readResponse->record;
    foreach($so2->customFieldList->customField as $customField){
            // var_dump($customField);
        if($customField->internalId=='165'){ // Order Uploaded Checkbox
            $customField->value = TRUE;
        }
    }

    $request = new UpdateRequest();
    $request->record = $so2;

    $service2->setPreferences(false, false, false, true);

    $updateResponse = $service2->update($request);
    if (!$updateResponse->writeResponse->status->isSuccess) {
            $msg = "UPDATE ERROR\n";
            error_log($msg);
            echo $msg;
    } else {
            echo "UPDATE SUCCESS, id " . $updateResponse->writeResponse->baseRef->internalId . "\n";
    }
}

function fishbowlOrderFromNetSuiteRecord($netsuite_record) {
    $order = new FbSalesOrder; // a sales order object to send'n'save in Fishbowl

    //$order->number = $netsuite_record->basic->tranId[0]->searchValue; // SONum
    $order->number = $netsuite_record->basic->tranId[0]->searchValue; // SONum
    $order->status = 20; // Status
    $order->customerName =  "Clubhub"; // CustomerName

    if (isset($netsuite_record->basic->shipAttention[0])) {
        $order->customerContact = $netsuite_record->basic->shipAttention[0]->searchValue; // CustomerContact
    } else {
        $msg = "Shipping Attention record missing for NetSuite Sales Order " . $order->number . ".\n";
        error_log($msg);
        echo $msg;
        return null; // returning null signifies to the calling script/object that this order had a problem.
    }

    $billTo = new FbPersonAddress;
    $billTo->name = "Clubhub";
    $billTo->addressField = "800 Commerce St";
    $billTo->city = "Las Vegas";
    $billTo->zip = "89106";
    $billTo->country = "UNITED STATES";
    $billTo->state = "NV";
    $order->billTo = $billTo;

    $shipTo = new FbPersonAddress;
    $shipTo->name = $netsuite_record->basic->shipAddressee[0]->searchValue . " Attn: " .
            $netsuite_record->basic->shipAttention[0]->searchValue;

    if (isset($netsuite_record->basic->shipAddress1[0])) {
        $shipTo->addressField = $netsuite_record->basic->shipAddress1[0]->searchValue;
    }
    if (isset($netsuite_record->basic->shipAddress2[0])) {
        $shipTo->addressField .= " " . $netsuite_record->basic->shipAddress2[0]->searchValue;
    }
    if (isset($netsuite_record->basic->shipAddress3[0])) {
        $shipTo->addressField .= " " . $netsuite_record->basic->shipAddress3[0]->searchValue;
    }

    $shipTo->city = $netsuite_record->basic->shipCity[0]->searchValue;
    $shipTo->state = $netsuite_record->basic->shipState[0]->searchValue;
    $shipTo->zip = $netsuite_record->basic->shipZip[0]->searchValue;
    $shipTo->country = "UNITED STATES";
    $order->shipTo = $shipTo;


    $order->carrier = "FedEx";
    $order->taxRateName = "none";
    $order->priorityId = 20;
    $order->createdDate = fixDate($netsuite_record->basic->tranDate[0]->searchValue);
    $order->salesman = "Keith";

    // Retrieve Shipping Method - Netsuite does not provide this for some reason
    $shipId = $netsuite_record->basic->shipMethod[0]->searchValue->internalId;
    $shipVia = shipService($shipId);
    $order->shippingTerms = "Prepaid &amp; Billed";

    $order->paymentTerms = "NET 30";
    $order->fob = "Origin";
    $order->note = $shipVia . ", kglynn1692@yahoo.com, 707-249-0801,";
    $order->quickBooksClassName = "Clubhub";
    $order->locationGroup =  "DTC";

    foreach($netsuite_record->basic->customFieldList->customField as $customField) {
        if($customField->internalId=='11'){ // Target Ship Date
            $order->firstShipDate =  fixDate($customField->searchValue);
        }
    }

    return $order;
}

function fishbowlOrderItemFromNetSuiteRecord($netsuite_record) {
    $item = new FbSalesOrderItem;
    $item->itemTypeId = 10; // SOItemTypeID

    // Club sets will have type == _assembly, other stuff will have type == _inventory
    $item->type = $netsuite_record->itemJoin->type[0]->searchValue;

    $item->productNumber = $netsuite_record->itemJoin->upcCode[0]->searchValue . "\0"; // ProductNumber
    $item->soid = $netsuite_record->basic->internalId[0]->searchValue->internalId;
    $item->quantity = $netsuite_record->basic->quantity[0]->searchValue; // ProductQuantity
    $item->uomCode = "ea"; // UOM
    $item->productPrice = "0"; // ProductPrice
    $item->taxable = "false"; // Taxable

    // Set the serial numbers on the item
    $serialsNote = "";
    $serialNums = $netsuite_record->basic->serialNumbers;
    if (isset($serialNums)) {
        foreach ($serialNums as $serialNum) {
            $serialsNote .= $serialNum->searchValue . " ";
        }
    }
    $item->serialNumbers = $serialsNote;

    $item->quickBooksClassName = "Clubhub"; // QuickBooksClassName
    foreach($netsuite_record->basic->customFieldList->customField as $customField) {
        if($customField->internalId=='11'){ // Target Ship Date
            $item->dateScheduledFulfillment =  fixDate($customField->searchValue); // FulfillmentDate
        }
    }
    $item->showItemFlag = "true"; // ShowItem
    $item->kitItemFlag = "false"; // KitItem

    return $item;
}

?>
