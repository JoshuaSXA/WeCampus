<?php
include_once '../lib/DBController.php';

$DBController = new DBController();

$DBController->connDatabase();


$patternID = $_REQUEST['pattern_id'];

$schedule = explode(",",$_REQUEST['schedule']);



for($i = 0; $i < count($schedule); ++$i) {

	$unitTime = $schedule[$i];

	$sql = "INSERT INTO schedule (pattern_id, time) VALUES($patternID, '$unitTime')";

	$retVal = mysqli_query($DBController->getConnObject(), $sql);

	if($retVal) {

		echo ($i . " " . $unitTime . " insertion succeeded!\n");

	} else {
		echo ($i . " " . $unitTime . " insertion failed!\n");
	}

}

$DBController->disConnDatabase();

?>