<?php
require_once('..\..\filemaker_api\server_data_request.php');
require_once('..\..\filemaker_api\FileMaker.php');


$recID = isset($_POST['record_id']) ? $_POST['record_id'] : '';
$toDB['_Request_ID'] = isset($_POST['request_id']) ? $_POST['request_id'] : '';
$toDB['_Service_ID'] = isset($_POST['service']['id']) ? $_POST['service']['id'] : '';
$toDB['_Container_Type_ID'] = isset($_POST['container']['id']) ? $_POST['container']['id'] : '';
$toDB['_Commodity_ID'] = isset($_POST['commodity']['id']) ? $_POST['commodity']['id'] : '';
$toDB['_PURFCommodityItemID'] = isset($_POST['item']['id']) ? $_POST['item']['id'] : '';
$toDB['Quantity'] = isset($_POST['quantity']) ? $_POST['quantity'] : '';
$toDB['Service Instructions'] = isset($_POST['instructions']) ? $_POST['instructions'] : '';
$edit = isset($_POST['edit']) ? $_POST['edit'] : '';

//print_r($_POST['edit']);

//create a new connection to filemaker
$fm = new FileMaker('Recycling', FM_IP, FM_USERNAME, FM_PASSWORD);

if ($edit == true) {
	//echo "editing action: ".$recID."\n";
	$newEdit =& $fm->newEditCommand('web_request_for_service_action', $recID, $toDB);
	$result = $newEdit->execute();
	$newRecord = current($result->getRecords());
	$recID = $newRecord->getRecordID();	
} else if ($edit == '') {
	//set the layout of our new find command
	$newAdd =& $fm->newAddCommand('web_request_for_service_action', $toDB);
	$result = $newAdd->execute();
	$newRecord = current($result->getRecords());
	$recID = $newRecord->getRecordID();
}

if (FileMaker::isError($result)) {
    $response['Error'] = $recID;
	$response = json_encode($response);
	echo $response;
} else {
	$response['Success'] = $recID;
	$response = json_encode($response);
	echo $response;
}

?>