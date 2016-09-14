<?php
require_once('..\..\filemaker_api\server_data_request.php');
require_once('..\..\filemaker_api\FileMaker.php');

$recID = isset($_POST['record_id']) ? $_POST['record_id'] : '';

//use record id of action to delete it
$fm = new FileMaker('Recycling', FM_IP, FM_USERNAME, FM_PASSWORD);
$rec = $fm->getRecordById('web_request_for_service_action', $recID);
$rec->delete();

?>