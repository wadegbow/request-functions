<?php
require_once('..\..\filemaker_api\server_data_request.php');
require_once('..\..\filemaker_api\FileMaker.php');

$date = date("m/d/Y");

$id = isset($_POST['id']) ? $_POST['id'] : '';
$recID = isset($_POST['recordId']) ? $_POST['recordId'] : '';
$context = isset($_POST['context']) ? $_POST['context'] : '';

switch ($context) {
	case "cancel":
		$context = 'Cancelled';
		break;
	case "submit":
		$context = 'Pending';
		break;
}

$toDB['Approval Status'] = $context;
$toDB['Surplus Credit'] = isset($_POST['credit']) ? $_POST['credit'] : '';
$toDB['Submitted_Date'] = $date;
$toDB['Service_Date'] = $date;

$fm = new FileMaker('Recycling', FM_IP, FM_USERNAME, FM_PASSWORD);

if ($context == 'duplicate') {
	$findCommand =& $fm->newFindCommand('web_request_for_service');
	$findCommand->addFindCriterion('RecID', $recID);
	$findCommand->setScript('web_duplicate_request');
	$result = $findCommand->execute();
	$newRecord = $result->getLastRecord();
	$fromDB['id'] = $newRecord->getField('_Request_ID');
	$fromDB['recordId'] = $newRecord->getRecordID();
	$fromDB['buildingID'] = $newRecord->getField('_Building_ID');
	$fromDB['buildingName'] = $newRecord->getField('Building');
	$fromDB['createDate'] = $newRecord->getField('Request Created Date');
	$fromDB['type'] = $newRecord->getField('RequestType');
	if ($fromDB['type'] == 'PURF') {
		$fromDB['type'] = 'Surplus';
	} else {
		$fromDB['type'] = 'Recycle';
	}
	
} else {
	$fromDB = null;
	
	$newEdit =& $fm->newEditCommand('web_request_for_service', $recID, $toDB);
	$result = $newEdit->execute();
	$newPerformScript = $fm->newPerformScriptCommand('web_request_for_service', 'Web_SetRequestActionDates', $id);
	$scriptResult = $newPerformScript->execute();
}
	
if (FileMaker::isError($result)) {
    $response['Error'] = $context;
	$response = json_encode($response);
	echo $response;
} else {
	$response['Success'] = array("context"=>$context,"returnInfo"=>$fromDB);
	$response = json_encode($response);
	echo $response;
}	

?>