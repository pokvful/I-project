<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/controllers/baseController.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/database/databaseHandler.php";

/**
 * Class TestGoogleMapsAPIController
 */
class TestGoogleMapsAPIController extends BaseController {

	//SPECIFICALLY FOR TESTING
	public function importAllLocationsItems() {
		$dbh = new DatabaseHandler();

		$getItemLocations = $dbh->query("SELECT city, country, item_number FROM Item");

		foreach ($getItemLocations as $getItemLocation) {
			$item_number = $getItemLocation["item_number"];
			$address = $getItemLocation["city"] . ' ' . $getItemLocation["country"];
			$address = str_replace(' ', '+', $address);

			bdump($this->getGeoCode($address));

			$longitude = substr($this->getGeoCode($address), strpos($this->getGeoCode($address), '+'));
			$latitude = substr($this->getGeoCode($address), -0, strpos($this->getGeoCode($address), '+'));

			$updateLatLongQuery = $dbh->query("UPDATE Item SET latitude = :latitude, longitude = :longitude WHERE item_number = :item_number", array(
				":latitude" => $latitude,
				":longitude" => $longitude,
				":item_number" => $item_number
			));
		}
	}

	//SPECIFICALLY FOR TESTING
	public function importAllLocationsUsers() {
		$dbh = new DatabaseHandler();

		$getUserLocations = $dbh->query("SELECT city, country, username FROM [User]");

		foreach ($getUserLocations as $getUserLocation) {
			$username = $getUserLocation["username"];
			$address = $getUserLocation["city"] . ' ' . $getUserLocation["country"];
			$address = str_replace(' ', '+', $address);

			bdump($this->getGeoCode($address));

			$longitude = substr($this->getGeoCode($address), strpos($this->getGeoCode($address), '+'));
			$latitude = substr($this->getGeoCode($address), -0, strpos($this->getGeoCode($address), '+'));

			$updateLatLongQuery = $dbh->query("UPDATE [User] SET latitude = :latitude, longitude = :longitude WHERE username = :username", array(
				":latitude" => $latitude,
				":longitude" => $longitude,
				":username" => $username
			));
		}
	}

	public function insertLatitudeAndLongitude() {
		$dbh = new DatabaseHandler();

		if (isset($_SESSION["username"])) {
			$username = $_SESSION["username"];

			$getLocations = $dbh->query("SELECT city, country FROM [User] WHERE username = :username", array(
				":username" => $username
			));

			$address = $getLocations[0]["city"] . ' ' . $getLocations[0]["country"];
			$address = str_replace(' ', '+', $address);

			bdump($this->getGeoCode($address));

			$longitude = substr($this->getGeoCode($address), strpos($this->getGeoCode($address), '+'));
			$latitude = substr($this->getGeoCode($address), -0, strpos($this->getGeoCode($address), '+'));

			$updateLatLongQuery = $dbh->query("UPDATE [User] SET latitude = :latitude, longitude = :longitude WHERE username = :username", array(
				":latitude" => $latitude,
				":longitude" => $longitude,
				":username" => $username
			));
		} else {
			$this->redirect('/');
		}
	}

	/**
	 * @param $address
	 * @return string
	 */
	public function getGeoCode($address) {
		//API KEY (Dit moet veiliger)
		$url = "https://maps.google.com/maps/api/geocode/json?address=$address&key=AIzaSyCbAYUeFKWJVsIt6kgwLE_359y7_pWCEsc";

		//Decodes json and returns latitude and longitude data
		$response = file_get_contents($url);
		$response = json_decode($response, true);
		$lat = $response['results'][0]['geometry']['location']['lat'];
		$long = $response['results'][0]['geometry']['location']['lng'];

		//Debugging purposes
		//		print_r($response);

		//		echo "latitude: " . $lat . " longitude: " . $long;
		return $lat . "+" . $long;
	}

	/**
	 * @param $lat1
	 * @param $long1
	 * @param $lat2
	 * @param $long2
	 * @return array
	 */
	public function calculateDistance($lat1, $long1, $lat2, $long2) {
		$lat1 = 51.979729;
		$long1 = 5.912400;
		$lat2 = 52.370216;
		$long2 = 4.895168;
		$theta = $long1 - $long2;
		$miles = (sin(deg2rad($lat1))) * sin(deg2rad($lat2)) + (cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)));
		$miles = acos($miles);
		$miles = rad2deg($miles);
		$result['miles'] = $miles * 60 * 1.1515;
		$result['feet'] = $result['miles'] * 5280;
		$result['yards'] = $result['feet'] / 3;
		$result['kilometers'] = $result['miles'] * 1.609344;
		bdump($result);
		return $result;
	}

	public function run() {
		//		$this->insertLatitudeAndLongitude();
		//		$this->importAllLocationsUsers();
		$this->importAllLocationsItems();
		$this->render();
	}
}
