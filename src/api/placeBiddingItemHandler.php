<?php

require_once $_SERVER["DOCUMENT_ROOT"] . '/src/database/databaseHandler.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/api/baseHandler.php';

class PlaceBiddingItemHandler extends BaseHandler {

	public function getGeoCode($address) {
		//TODO:API KEY (Dit moet veiliger)
		$url = "https://maps.google.com/maps/api/geocode/json?address=$address&key=AIzaSyCbAYUeFKWJVsIt6kgwLE_359y7_pWCEsc";

		//Decodes json and returns latitude and longitude data
		$response = file_get_contents($url);
		$response = json_decode($response, true);
		$lat = $response['results'][0]['geometry']['location']['lat'];
		$long = $response['results'][0]['geometry']['location']['lng'];

		return $lat . "+" . $long;
	}

	public function run() {
		$dbh = new DatabaseHandler();
		if (isset($_POST["place_bid"])) {
			$checkIfUserIsSellerQuery = $dbh->query("SELECT seller FROM [User] WHERE seller = 1 AND username = :username", array(
				":username" => $_SESSION["username"]
			));

			if (count($checkIfUserIsSellerQuery) > 0) {
				$title = $_POST["title"];
				$seller = $_SESSION["username"];
				$description = $_POST["description"];
				$starting_price = $_POST["price"];
				$payment_method = $_POST["payment_method"];
				$payment_instruction = $_POST["payment_instruction"];
				$shipping_cost = $_POST["shipping_costs"];
				$shipping_instructions = $_POST["shipping_instruction"];
				$rubric = $_POST["category"];

				bdump($_FILES);
				//file
				$files = array_filter($_FILES['upload']['name']); //Use something similar before processing files.
				// Count the number of uploaded files in array
				$total_count = count($_FILES['upload']['name']);
				// Loop through every file
				for ($i = 0; $i < $total_count; $i++) {
					//The temp file path is obtained
					$tmpFilePath = $_FILES['upload']['tmp_name'][$i];
					//A file path needs to be present
					if ($tmpFilePath != "") {
						//Setup our new file path
						$newFilePath = "./resources/images/bidding/" . $_FILES['upload']['name'][$i];
						//File is uploaded to temp dir
						if (move_uploaded_file($tmpFilePath, $newFilePath)) {
							$file_name = $files[0];
						}
					}
				}

				$city = $_POST["city"];
				$country = $_POST["country"];
				$duration = $_POST["duration"];
				$auction_closed = 0;

				$getItemLocations = $dbh->query("SELECT city, country, item_number FROM Item");

				$item_number = $getItemLocations[0]["item_number"];
				$address = $_POST["city"] . '+' . $_POST["country"];

				$result = $this->getGeoCode($address);

				$latitude = substr($result, 0, strpos($result, '+'));
				$longitude = substr($result, strlen($latitude) + 1);

				$placeItemQuery = $dbh->query(
					<<<SQL
						INSERT INTO Item (title, [description], starting_price, payment_method, payment_instruction, city, country, duration, duration_start_day, duration_start_time, shipping_cost, shipping_instructions, seller, duration_end_day, duration_end_time, auction_closed, latitude, longitude)
						VALUES(:title, :description, :starting_price, :payment_method, :payment_instruction, :city, :country, :duration, getDate(), CONVERT(TIME, getDate()), :shipping_cost, :shipping_instruction, :seller, DATEADD(DD, $duration, getDate()), CONVERT(TIME, getDate()), :auction_closed, :latitude, :longitude) 
						SQL,
					array(

						":title" => $title,
						":description" => $description,
						":starting_price" => $starting_price,
						":payment_method" => $payment_method,
						":payment_instruction" => $payment_instruction,
						":city" => $city,
						":country" => $country,
						":duration" => $duration,
						":shipping_cost" => $shipping_cost,
						":shipping_instruction" => $shipping_instructions,
						":seller" => $seller,
						":auction_closed" => $auction_closed,
						":latitude" => $latitude,
						":longitude" => $longitude
					)
				);

				$getItemNumberQuery = $dbh->query("SELECT @@IDENTITY AS highestID");

				$placeMediaQuery = $dbh->query(
					<<<SQL
						INSERT INTO [File] ([filename], item)
						VALUES(:file_name, :item_number)
						SQL,
					array(
						":file_name" => $file_name,
						"item_number" => $getItemNumberQuery[0]["highestID"]
					)
				);

				$placeItemInRubricQuery = $dbh->query(
					<<<SQL
						INSERT INTO Item_in_rubric (item, rubric_at_lowest_level)
							VALUES (:item, :rubric)
					SQL,
					array(
						":item" => $getItemNumberQuery[0]["highestID"],
						":rubric" => $rubric,
					)
				);
			} else {
				$this->redirect(
					"/placeBiddingItem/?bidding-error=" . urlencode("U moet een verkoper zijn om een item te plaatsen.")
				);
			}


			$this->redirect(
				"/placeBiddingItem/?bidding-success=" . urlencode("Veiling is succesvol geplaatst.")
			);
		}
	}
}
