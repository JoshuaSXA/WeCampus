<?php


include_once '../server/lib/DBController.php';

$DBController = new DBController();

$DBController->connDatabase();


$patternModel = explode(",", $_REQUEST['pattern_model']);

$patternSpan = explode(",", $_REQUEST['pattern_span']);

if(count($patternModel) != count($patternSpan)) {

	echo "pattern num should be equal to the span num!";

}


$multiSchedule = count($patternModel) == 2 ? TRUE : FALSE;

global $routeID; global $stationID; global $startDate; global $weeks;

$routeID = $_REQUEST['route_id'];

$stationID = $_REQUEST['station_id'];

//开始日期,开始日期必须是周一
$startDate = $_REQUEST['start_date'];

$weeks = (int)$_REQUEST['week_num'];




if($multiSchedule) {

	multiPatternInsert($patternModel, $patternSpan, $DBController->getConnObject());

} else {

	singlePatternInsert((int)$patternModel[0], $DBController->getConnObject());

}




function singlePatternInsert($patternID, &$conn) {
	global $routeID; global $stationID; global $startDate; global $weeks;

	$fromDate = $startDate;

	$dateSpan = '+' . ($weeks * 7 - 1) . ' day';  

	$endDate = date('Y-m-d', strtotime(date('Y-m-d', strtotime($fromDate)).$dateSpan));


	$sql = "INSERT INTO date_pattern (from_date, to_date, station_id, route_id, pattern_id) 
			VALUES('$fromDate', '$endDate', $stationID, $routeID, $patternID)";

	$retVal = mysqli_query($conn, $sql);

	if($retVal) {

		echo "single pattern insertion succeeded!";

	} else {

		echo "single pattern insertion failed!";

	}

}


function multiPatternInsert($patternModel, $patternSpan, &$conn) {

	global $routeID; global $stationID; global $startDate; global $weeks;

	$fromDate = $startDate;

	$endDate = '';

	for($i = 0; $i < $weeks; ++$i) {

		for($j = 0; $j < count($patternModel); ++$j) {

			$patternID = (int)$patternModel[$j];

			$dateSpan = '+' . ((int)$patternSpan[$j] - 1) . ' day';

			$endDate = date('Y-m-d', strtotime(date('Y-m-d', strtotime($fromDate)).$dateSpan));

			$sql = "INSERT INTO date_pattern (from_date, to_date, station_id, route_id, pattern_id) 
			 		VALUES('$fromDate', '$endDate', $stationID, $routeID, $patternID)";

			$retVal = mysqli_query($conn, $sql);

			if($retVal) {

				echo ($i + 1) . " weeks " . $fromDate . " to " . $endDate . " insertion succeeded!\n";

			} else {

				echo ($i + 1) . " weeks " . $fromDate . " to " . $endDate . " insertion failed\n";

			}

			$fromDate = date('Y-m-d', strtotime(date('Y-m-d', strtotime($endDate)).'+1 day'));

		}

		

	}

}


?>