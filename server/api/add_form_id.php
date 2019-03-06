<?php

include_once '../lib/FormidPoolController.php';

/*****************************************************
 *
 * 该api用来向formid缓存池中增加一条formId
 *
 ****************************************************/

$openID = $_REQUEST['open_id'];

$formID = $_REQUEST['form_id'];

# $moduleTag = $_REQUEST['module_tag'];
$module_tag = "formId";

$formIDList = explode(",",$formID);

$formIdPoolControllerObj = new FormidPoolController($moduleTag, $openID);


for($i = 0; $i < count($formIDList); ++$i){

	$formIdPoolControllerObj->addFormId($formID);
	
}


echo json_encode(array("success" => TRUE));