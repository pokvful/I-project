<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/controllers/baseController.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/database/databaseHandler.php";

class TestGoogleMapsAPIController extends BaseController {

	public $long;
	public $lat;

	public function getLatitudeAndLongitude() {
		$dbh = new DatabaseHandler();
		$getLocations = $dbh->query("SELECT TOP 10 city, country FROM [User]");

		//Prints output of query
		bdump($getLocations);

		//Encode sql output to json
		$output = json_encode($getLocations, true);

		echo '<div id="data">' . $output . '</div>';
	}

	public function setLat($lat) {
		$this->lat = $lat;
	}

	public function setLng($long) {
		$this->long = $long;
	}

	public function updateLatitudeAndLongitude() {
		$dbh = new DatabaseHandler();
		$getLocations = $dbh->query("UPDATE [User] SET latitude = :latitude, longitude = :longitude", array(
			":latitude" => $this->lat,
			":longitude" => $this->long
		));
	}


	public function calculateDistance($lat1, $long1, $lat2, $long2) {
		$theta = $long1 - $long2;
		$miles = (sin(deg2rad($lat1))) * sin(deg2rad($lat2)) + (cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)));
		$miles = acos($miles);
		$miles = rad2deg($miles);
		$result['miles'] = $miles * 60 * 1.1515;
		$result['feet'] = $result['miles'] * 5280;
		$result['yards'] = $result['feet'] / 3;
		$result['kilometers'] = $result['miles'] * 1.609344;
		return $result;
	}

	public function run() {
		$this->getLatitudeAndLongitude();
		$this->render();
	}
}
