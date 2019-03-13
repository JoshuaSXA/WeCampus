<?php

include_once '../lib/DBController.php';

$DBController = new DBController();

$DBController->connDatabase();


$patternID = $_REQUEST['pattern_id'];

$sql = "DELETE FROM schedule WHERE pattern_id = " . $patternID;

$retVal = mysqli_query($DBController->getConnObject(), $sql);

if($retVal) {

	echo "delete succeeded!";

} else {
	//echo $DBController->getErrorCode();
	echo "delete failed!";
}

$DBController->disConnDatabase();
?>