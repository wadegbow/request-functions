<?php
header('Content-type: application/javascript');

require_once('..\..\filemaker_api\server_data_request.php');
require_once('..\..\filemaker_api\FileMaker.php');

//create a new connection to filemaker
$fm = new FileMaker('Recycling', FM_IP, FM_USERNAME, FM_PASSWORD);

//set the layout of our new find command
$findCommand =& $fm->newFindCommand('web_request_for_service_action');
//add the search criteria
$findCommand->addFindCriterion('_Request_ID', $_GET['id']);
$result = $findCommand->execute();

if (FileMaker::isError($result)) {
    $actions = null;
} else {
	$count = $result->getFoundSetCount();
	$requestActionList = $result->getRecords();

	//loop through the actions associated with the request
	//setup json
	$i = 0;
	foreach($requestActionList as $actionList) {
		$actions[$i]['record_id'] = $actionList->getField('_Record_ID');
		$actions[$i]['request_id'] = $actionList->getField('_Request_ID');
		$actions[$i]['service'] = array("id"=>$actionList->getField('_Service_ID'),"name"=>html_entity_decode($actionList->getField('Service Name')));
		$actions[$i]['container'] = array("id"=>$actionList->getField('_Container_Type_ID'),"name"=>$actionList->getField('Container Type'));
		$actions[$i]['commodity'] = array("id"=>$actionList->getField('_Commodity_ID'),"name"=>html_entity_decode($actionList->getField('Commodity_Name_Web')));
		$actions[$i]['item'] = array("id"=>$actionList->getField('_PURFCommodityItemID'),"name"=>$actionList->getField('PURFItemName'));
		$actions[$i]['quantity'] = $actionList->getField('Quantity') + 0;
		$actions[$i]['instructions'] = $actionList->getField('Service Instructions');

		$i++;
	}
}

//configure the new search
$findCommand =& $fm->newFindCommand('web_request_for_service_account');
//add the search criteria
$findCommand->addFindCriterion('_Request_ID', $_GET['id']);
$result = $findCommand->execute();

if (FileMaker::isError($result)) {
	$accounts = null;
} else {
	$count = $result->getFoundSetCount();
	$requestAccountList = $result->getRecords();

	$invalid_prefix =  array('AA', 'AB', 'AM', 'AN', 'AR', 'AS', 'AU', 'DN', 'DS', 'DT', 'DY', 'GV', 'PN', 'RY', 'XA', 'XH', 'XT');

	$i = 0;
	foreach($requestAccountList as $accountList) {
		$relatedSet = $accountList->getRelatedSet('RequestForServiceAccounts to Customer Account');
		foreach ($relatedSet as $related) {
			$accounts[$i]['recordId'] = $related->getRecordId();
		}
		$accounts[$i]['request_id'] = $accountList->getField('_Request_ID');
		$accounts[$i]['recID'] = $accountList->getField('_Record_ID');
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
}

$actions = json_encode($actions);
$accounts = json_encode($accounts);

echo $_GET['callback'].'({"actions": '.$actions.', "accounts": '.$accounts.'});';

?>