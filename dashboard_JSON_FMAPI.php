<?php
header('Content-type: application/javascript');
set_time_limit ( 60 );

require_once('..\..\filemaker_api\server_data_request.php');
require_once('..\..\filemaker_api\FileMaker.php');
error_reporting(E_ALL);
session_start();
//include('includes/top.php');

    //create new filemaker connection
    $fm = new FileMaker('Recycling', FM_IP, FM_USERNAME, FM_PASSWORD);

    //set the find command to customers layout
    $findCommand = $fm->newFindCommand('web_customers');
    $findCommand->addFindCriterion('Active', '==Yes');
    $findCommand->addFindCriterion('InternalExternal', '==Internal');
    $findCommand->addFindCriterion('NetID', "==".$_GET['netID']);
    $result = $findCommand->execute();

    if (FileMaker::isError($result)) {
        echo $result->getMessage();
        $count = 0;
    } else {
        $contactsList = $result->getRecords();
        $count = $result->getFoundSetCount();
    }

    $currentCustomer = array();

    if ($count > 0) {
        $currentCustomer['authenticated'] = 'yes';
        foreach($contactsList as $contact) {
            $currentCustomer['id'] = $contact->getField('_Customer_ID');
            $currentCustomer['RecID'] = $contact->getField('recID');
            $currentCustomer['name'] = $contact->getField('First')." ".$contact->getField('Last');
            $currentCustomer['netid'] = $contact->getField('NetID');
            $currentCustomer['email'] = $contact->getField('Email');
            $currentCustomer['phone'] = $contact->getField('Phone');
            if (substr($contact->getField('NetID'), 0, 5) == "CLEAN") {
                $currentCustomer['type'] = 'custodial';
            } else {
                $currentCustomer['type'] = 'customer';
            }
            $currentCustomer['building'] = $contact->getField('_Building_ID');

        }

        if (strlen($currentCustomer['id']) > 0) {
            $findCommand = $fm->newFindCommand('web_request_for_service');
            $findCommand->addFindCriterion('_Customer_ID', $currentCustomer['id']);
            $findCommand->addSortRule('Request Created Date', 1, FILEMAKER_SORT_DESCEND);
            $findCommand->addSortRule('_Request_ID', 1, FILEMAKER_SORT_DESCEND);
            $findCommand->setRange(0,30);
            $result = $findCommand->execute();

            if (FileMaker::isError($result)) {
                $previousRequests = null;
            } else {
                $requestsList = $result->getRecords();

                $i = 0;
                $previousRequests = array();

                $preSubmit = array('initiated web request');
                $postSubmit = array('in progress', 'approved', 'pending');
                foreach($requestsList as $request) {
                    $id = $request->getField('_Request_ID');
                    $previousRequests[$i]['id'] = $request->getField('_Request_ID');
                    $previousRequests[$i]['recordId'] = $request->getField('RecID');
                    $previousRequests[$i]['createDate'] = $request->getField('Request Created Date');
                    $previousRequests[$i]['buildingID'] = $request->getField('_Building_ID');
                    $previousRequests[$i]['buildingName'] = ucwords($request->getField('Building'));
                    $previousRequests[$i]['status'] = strtolower($request->getField('Approval Status'));
                    $previousRequests[$i]['delivery'] = $request->getField('purf_delivery');
                    $previousRequests[$i]['type'] = $request->getField('RequestType');
                    $previousRequests[$i]['popover'] = '';

                    if($previousRequests[$i]['type'] != 'PURF') {
                        $previousRequests[$i]['type'] = 'Recycle';
                    } else {
                        $previousRequests[$i]['type'] = 'Surplus';
                    }

                    if(in_array($previousRequests[$i]['status'], $preSubmit)) {
                        $previousRequests[$i]['displayStatus'] = 'not submitted';
                    } else if (in_array($previousRequests[$i]['status'], $postSubmit)) {
                        $previousRequests[$i]['displayStatus'] = 'submitted';
                    } else {
                        $previousRequests[$i]['displayStatus'] = $previousRequests[$i]['status'];
                    }

                    //set the layout of our new find command
                    $actionFindCommand =& $fm->newFindCommand('web_request_for_service_action');
                    //add the search criteria
                    $actionFindCommand->addFindCriterion('_Request_ID', $previousRequests[$i]['id']);
                    $actionResult = $actionFindCommand->execute();

                    if (FileMaker::isError($actionResult)) {
                        //echo "Error: " . $result->getMessage() . "\n";
                        $actions = null;
                        $previousRequests[$i]['popover'] .= 'This request has no actions.';
                    } else {
                        $count = $actionResult->getFoundSetCount();
                        $requestActionList = $actionResult->getRecords();

                        $n = 0;
                        //$actions = array();

                        foreach($requestActionList as $actionList) {
                            $actions[$n]['record_id'] = $actionList->getField('_Record_ID');
                            $actions[$n]['request_id'] = $actionList->getField('_Request_ID');
                            $actions[$n]['service'] = array("id"=>$actionList->getField('_Service_ID'),"name"=>html_entity_decode($actionList->getField('Service Name')));
                            $actions[$n]['container'] = array("id"=>$actionList->getField('_Container_Type_ID'),"name"=>$actionList->getField('Container Type'));
                            $actions[$n]['commodity'] = array("id"=>$actionList->getField('_Commodity_ID'),"name"=>html_entity_decode($actionList->getField('Commodity_Name_Web')));
                            $actions[$n]['item'] = array("id"=>$actionList->getField('_PURFCommodityItemID'),"name"=>$actionList->getField('PURFItemName'));
                            $actions[$n]['quantity'] = $actionList->getField('Quantity') + 0;
                            $actions[$n]['instructions'] = $actionList->getField('Service Instructions');

                            $previousRequests[$i]['popover'] .= isset($actions[$n]['service']['name']) ? $actions[$n]['service']['name'] : '';
                            $previousRequests[$i]['popover'] .= ': ';
                            $previousRequests[$i]['popover'] .= isset($actions[$n]['container']['name']) ? $actions[$n]['container']['name'] : '';
                            $previousRequests[$i]['popover'] .= ' - ';
                            $previousRequests[$i]['popover'] .= isset($actions[$n]['item']['name']) ? $actions[$n]['item']['name'] : '';
                            $previousRequests[$i]['popover'] .= ' x';
                            $previousRequests[$i]['popover'] .= isset($actions[$n]['quantity']) ? $actions[$n]['quantity'] : '';
                            $previousRequests[$i]['popover'] .= '<br>';

                            $n++;
                        }
                    }

                    $previousRequests[$i]['actions'] = isset($actions) ? $actions : null;

                    $i++;
                }
            }

            $findCommand = $fm->newFindCommand('web_customer_account');
            $findCommand->addFindCriterion('_Customer_ID', $currentCustomer['id']);
            $findCommand->addFindCriterion('Status', 'Yes');
            $result = $findCommand->execute();

            if (FileMaker::isError($result)) {
                //echo $result->getMessage();

                $accounts = null;
            } else {
                $requestAccountList = $result->getRecords();

                $invalid_prefix =  array('AA', 'AB', 'AM', 'AN', 'AR', 'AS', 'AU', 'DN', 'DS', 'DT', 'GV', 'PN', 'XA', 'XH', 'XT');

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
            }
        }

        $currentCustomer = json_encode($currentCustomer);
        $previousRequests = json_encode($previousRequests, JSON_NUMERIC_CHECK);
        $accounts = json_encode($accounts);

        echo $_GET['callback'].'({"customer": '.$currentCustomer.', "requests": '.$previousRequests.', "accounts": '.$accounts.'});';
    } else {
        $currentCustomer['authenticated'] = 'no';
    }
?>
