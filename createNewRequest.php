<?php
require_once('..\..\filemaker_api\server_data_request.php');
require_once('..\..\filemaker_api\FileMaker.php');
error_reporting(E_ALL);
session_start();

//setup the array getting sent to the database
$toDB['_Customer_ID'] = isset($_POST['info']['customer']) ? $_POST['info']['customer'] : '';
$toDB['_Building_ID'] = isset($_POST['info']['buildingID']) ? $_POST['info']['buildingID'] : '';
$toDB['Approval Status'] = 'Initiated Web Request';
$toDB['RequestType'] = isset($_POST['info']['type']) ? $_POST['info']['type'] : '';
$toDB['purf_delivery'] = isset($_POST['info']['delivery']) ? $_POST['info']['delivery'] : '';

//convert to internal lingo
switch (strtolower($toDB['RequestType'])) {
	case "recycle":
		$toDB['RequestType'] = 'Recycling';
		break;
	case "surplus":
		$toDB['RequestType'] = 'PURF';
		break;
}

//create a new connection to filemaker
$fm = new FileMaker('Recycling', FM_IP, FM_USERNAME, FM_PASSWORD);

//create the new record
$newAdd =& $fm->newAddCommand('web_request_for_service', $toDB);
$result = $newAdd->execute();
$newRecord = current($result->getRecords());
$recID = $newRecord->getRecordID();
$reqID = $newRecord->getField('_Request_ID');
$createDate = $newRecord->getField('Request Created Date');

//check for errors
if (FileMaker::isError($result)) {
    $response['Error'] = $recID;
	$response = json_encode($response);
	echo $response;
} else {
	$response['Success'] = array("recID"=>$recID, "reqID"=>$reqID, "createDate"=>$createDate);
	$response = json_encode($response);
	echo $response;
}

?>