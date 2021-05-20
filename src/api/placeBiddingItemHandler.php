<?php

require_once $_SERVER["DOCUMENT_ROOT"] . '/src/database/databaseHandler.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/api/baseHandler.php';

class PlaceBiddingItemHandler extends BaseHandler {

	public function run() {
		$dbh = new DatabaseHandler();
		if ($_SESSION["loggedin"]) {
			$checkIfUserIsSellerQuery = $dbh->query("SELECT seller FROM [User] WHERE seller = 1 AND username = :username", array(
				":username" => $_SESSION["username"]
			));
			if (count($checkIfUserIsSellerQuery) > 0) {

				$title = $_POST["title"];
				$seller = $_SESSION["username"];
				$description = $_POST["description"];
				$starting_price = $_POST["price"];
				$payment_method = $_POST["payment_method"];
				$payment_instructions = $_POST["payment_instruction"];
				$shipping_cost = $_POST["shipping_costs"];
				$shipping_instructions = $_POST["shipping_instruction"];
//				$file_name = $_POST["file_name"];

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
						$newFilePath = "./uploadFiles/" . $_FILES['upload']['name'][$i];
						//File is uploaded to temp dir
						if (move_uploaded_file($tmpFilePath, $newFilePath)) {
							//Other code goes here
						}
					}
				}

				// end file

				$getSellerLocationQuery = $dbh->query("
		SELECT city, country FROM [User] WHERE username = :username
		", array(
					":username" => $seller
				));

				$city = $getSellerLocationQuery[0]["city"];
				$country = $getSellerLocationQuery[0]["country"];

				$placeItemQuery = $dbh->query(
					<<<SQL
				INSERT INTO Item (title, [description], starting_price, payment_method, payment_instructions, city, country, duration, duration_start_day, duration_start_time, shipping_cost, shipping_instructions, seller, duration_end_day, duration_end_time)
				VALUES(:title, :description, :starting_price, :payment_method, :payment_instructions, :city, :country, :duration, :duration_start_day, :duration_start_time, :shipping_cost, :shipping_instructions, :seller, :duration_end_day, :duration_end_time) 
			SQL,

					array(
						":title" => $title,
						":description" => $description,
						":starting_price" => $starting_price,
						":payment_method" => $payment_method,
						":payment_instructions" => $payment_instructions,
						":city" => $city,
						":country" => $country,
						":duration" => 4,
						":duration_start_day" => getDate(),
						":duration_start_time" => CONVERT(TIME, getDate()),
						"shipping_cost" => $shipping_cost,
						":shipping_instruction" => $shipping_instructions,
						":seller" => $seller,
						"duration_end_day" => DATEADD(DD, 4, @Date),
						"duration_end_time" => DATEADD(HOUR, 4, @Date)
					));

				$placeMediaQuery = $dbh->query(
					<<<SQL
			INSERT INTO [File] ([filename], item) 
				VALUES(:file_name, :item_number) 
			SQL,

					array(
						":file_name" => $file_name,
						"item_number" => $item_number
					));
			}
		}
		$this->redirect("/placeBiddingItem/?bidding-success=" . urlencode("Bod is succesvol geplaatst.")
		);
	}
}