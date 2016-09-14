<?php
require_once('..\..\filemaker_api\server_data_request.php');
require_once('..\..\filemaker_api\FileMaker.php');

$fm = new FileMaker('Recycling', FM_IP, FM_USERNAME, FM_PASSWORD);

$toDB['_Customer_ID'] = isset($_POST['customer']) ? $_POST['customer'] : '';
$toDB['Account Number'] = isset($_POST['number']) ? $_POST['number'] : '';
$toDB['Sub Account'] = isset($_POST['subAccount']) ? $_POST['subAccount'] : '';

$invalid_prefix =  array('AA', 'AB', 'AM', 'AN', 'AR', 'AS', 'AU', 'DN', 'DS', 'DT', 'GV', 'PN', 'XA', 'XH', 'XT');

$newAdd =& $fm->newAddCommand('web_customer_account', $toDB);
$result = $newAdd->execute();           

if (FileMaker::isError($result)) {
    $response['Error'] = 'error connecting to db';
	$response = json_encode($response);
	echo $response;
} else {

	$requestAccountList = current($result->getRecords());

	$i = 0;
	foreach($requestAccountList as $accountList) {
		$accounts[$i]['request_id'] = $accountList->getField('_Request_ID');
		$accounts[$i]['recordId'] = $accountList->getField('_Record_ID');
		$accounts[$i]['serial'] = $accountList->getField('_CustAcct_Serial');
		$accounts[$i]['number'] = $accountList->getField('Account Number');
		$accounts[$i]['subAccount'] = $accountList->getField('Sub Account');
		$accounts[$i]['subObject'] = $accountList->getField('Sub Object');
		$accounts[$i]['projectCode'] = $accountList->getField('Project Code');
		$accounts[$i]['orgRefId'] = $accountList->getField('OrgRefID');
		$accounts[$i]['percent'] = $accountList->getField('Percentage Entry') + 0;
		$prefix[$i] = preg_split('/[0-9]{2}/', $accounts[$i]['number']);
		if (!in_array($prefix[$i][0], $invalid_prefix)) {
			$accounts[$i]['credit'] = 'No';
		} else {
			$accounts[$i]['credit'] = 'Yes';
		}
		$i++;
	} 

	$response['Success'] = $accounts[0];
	$response = json_encode($response);
	echo $response;
}	

?>