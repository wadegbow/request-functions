<?php
require_once('..\..\filemaker_api\server_data_request.php');
require_once('..\..\filemaker_api\FileMaker.php');

$recID = isset($_POST['recID']) ? $_POST['recID'] : '';

//use record id of account to delete it
$fm = new FileMaker('Recycling', FM_IP, FM_USERNAME, FM_PASSWORD);
$rec = $fm->getRecordById('web_request_for_service_account', $recID);
$rec->delete();

?>