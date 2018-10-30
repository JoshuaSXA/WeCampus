<?php
include_once '../server/lib/DBController.php';

$DBController = new DBController();

$DBController->connDatabase();


$patternID = $_REQUEST['pattern_id'];

$schedule = $_REQUEST['schedule'];


for($i = 0; $i < count($schedule); ++$i) {

	$unitTime = $schedule[$i];

	$sql = "INSERT INTO schedule (pattern_id, time) VALUES($patternID, '$unitTime')";

	$retVal = mysqli_query($DBController->getConnObject(), $sql);

	if($retVal) {

		echo $i + " insertion succeeded!";

	} else {
		echo $i + " insertion failed!";
	}

}

$DBController->disConnDatabase();

?>