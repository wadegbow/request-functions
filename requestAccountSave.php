<?php
require_once('..\..\filemaker_api\server_data_request.php');
require_once('..\..\filemaker_api\FileMaker.php');

$recID = isset($_POST['recID']) ? $_POST['recID'] : '';
$toDB['_Request_ID'] = isset($_POST['request_id']) ? $_POST['request_id'] : '';
$toDB['_Customer_ID'] = isset($_POST['customer_id']) ? $_POST['customer_id'] : '';
$toDB['_CustomerAccount_Serial'] = isset($_POST['serial']) ? $_POST['serial'] : '';
$toDB['Sub Object'] = isset($_POST['subObject']) ? $_POST['subObject'] : '';
$toDB['Project Code'] = isset($_POST['projectCode']) ? $_POST['projectCode'] : '';
$toDB['OrgRefID'] = isset($_POST['orgRefId']) ? $_POST['orgRefId'] : '';
$toDB['Percentage Entry'] = isset($_POST['percent']) ? $_POST['percent'] : '';
$edit = isset($_POST['edit']) ? $_POST['edit'] : '';

//create a new connection to filemaker
$fm = new FileMaker('Recycling', FM_IP, FM_USERNAME, FM_PASSWORD);

if ($edit == true) {
	echo "editing action: ".$recID."\n";
	$newEdit =& $fm->newEditCommand('web_request_for_service_account', $recID, $toDB);
	$result = $newEdit->execute();

} else if ($edit == '') {
	//set the layout of our new add command
	$newAdd =& $fm->newAddCommand('web_request_for_service_account', $toDB);
	$result = $newAdd->execute();

}

if (FileMaker::isError($result)) {
    $response['Error'] = $recID;
	$response = json_encode($response);
	echo $response;
} else {
	$newRecord = current($result->getRecords());
	$recID = $newRecord->getRecordID();
	
	$response['Success'] = $recID;
	$response = json_encode($response);
	echo $response;
}

?>