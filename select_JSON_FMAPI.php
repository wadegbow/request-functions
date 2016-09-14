<?php
header('Content-type: application/javascript');

require_once('..\..\filemaker_api\server_data_request.php');
require_once('..\..\filemaker_api\FileMaker.php');
error_reporting(E_ALL);

// Create a new connection to database.
$fm = new FileMaker('Recycling', FM_IP, FM_USERNAME, FM_PASSWORD);

// Create FileMaker_Command_Find on layout to search
$findCommand =& $fm->newFindCommand('web_buildings');
$findCommand->addFindCriterion('Status', 'Active');
$findCommand->addFindCriterion('Web_Request_Display', 'Yes');
// Sort records in descending 'Title' order
$findCommand->addSortRule('Building Name', 1, FILEMAKER_SORT_ASCEND);
// Execute find command
$result = $findCommand->execute();

if (FileMaker::isError($result)) {
    echo "Error: " . $result->getMessage() . "\n";
    exit;
}

// Get array of found records
$buildingList = $result->getRecords();

    $i = 0;
    foreach($buildingList as $building) {
        $id =  $building->getField('_Building_ID');
        $name = $building->getField('Building Name');
		$name = html_entity_decode($name);
        $buildingOptions[$i] = array("id"=>$id, "name"=>$name);
        $i++;
    }
	
$findCommand =& $fm->newFindCommand('web_items');
$findCommand->addFindCriterion('Status', 'Active');
$findCommand->addFindCriterion('_CommodityID', '63');
$findCommand->addSortRule('PURFItemName', 1, FILEMAKER_SORT_ASCEND);
$result = $findCommand->execute();
$purfItemList = $result->getRecords();

    $i = 0;
    foreach($purfItemList as $purfItem) {
        $id = $purfItem->getField('_PURFCommodityItemID');
        $name = $purfItem->getField('PURFItemName');
		$name = html_entity_decode($name);
        $purfItemOptions[$i] = array("id"=>$id, "name"=>$name);
        $i++;
    }
	
$findCommand =& $fm->newFindCommand('web_container');
$findCommand->addFindCriterion('Web Request Type', 'Recycling');
$findCommand->addSortRule('Container Type Active', 1, FILEMAKER_SORT_ASCEND);
$result = $findCommand->execute();
$containerList = $result->getRecords();
	
    $i = 0;
    foreach($containerList as $container) {
        $id = $container->getField('_Container_Type_ID Active');
        $name = $container->getField('Container Type Active');
		$name = html_entity_decode($name);
        $containerOptions[$i] = array("id"=>$id, "name"=>$name);
        $i++;
    }	
	
$findCommand =& $fm->newFindCommand('web_service');
$findCommand->addFindCriterion('Web Request Type', 'Recycling');
$findCommand->addSortRule('Service Name Active', 1, FILEMAKER_SORT_ASCEND);
$result = $findCommand->execute();
$serviceList = $result->getRecords();

	$i = 0;
	foreach($serviceList as $service) {
        $id = $service->getField('_Service_ID Active');
        $name = $service->getField('Service Name Active');
		$name = html_entity_decode($name);
        $serviceOptions[$i] = array("id"=>$id, "name"=>$name);
        $i++;
	}

$findCommand =& $fm->newFindCommand('web_commodity');
$findCommand->addFindCriterion('Web Request Type', 'Recycling');
$findCommand->addFindCriterion('Pickup Status', 'Active');
$findCommand->addSortRule('Commodity Description ActivePickup', 1, FILEMAKER_SORT_ASCEND);
$result = $findCommand->execute();
$commodityList = $result->getRecords();
	
    $i = 0;
    foreach($commodityList as $commodity) {
        $id = $commodity->getField('_CommodityIDActivePickup');
        $name = $commodity->getField('Commodity Description ActivePickup');
		$name = html_entity_decode($name);
        $commodityOptions[$i] = array("id"=>$id, "name"=>$name);
        $i++;
    } 	

    $buildingOptions = json_encode($buildingOptions);
    $purfItemOptions = json_encode($purfItemOptions);
    $containerOptions = json_encode($containerOptions);
    $serviceOptions = json_encode($serviceOptions);
    $commodityOptions = json_encode($commodityOptions);
	
	$callback = isset($_GET['callback']) ? $_GET['callback'] : '';

    if ($callback) {
        echo $callback.'({"buildings":'.$buildingOptions.', "items":'.$purfItemOptions.', "containers":'.$containerOptions.', "services":'.$serviceOptions.', "commodities":'.$commodityOptions.'});';
    } else  {
        echo 'who are you?';
    }

?>