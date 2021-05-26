<?php

require_once $_SERVER["DOCUMENT_ROOT"] . '/src/database/databaseHandler.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/api/baseHandler.php';

class ChangeProfileHandler extends BaseHandler {
	public function run() {
		$dbh = new DatabaseHandler();

		$Email = $_POST["E-mail"];
		$firstName = $_POST["firstName"];
		$lastName = $_POST["lastName"];
		$birthDate = $_POST["birthdate"];
		$adress1 = $_POST["Adress1"];
		$adress2 = $_POST["Adress2"];
		$zipCode = $_POST["PostalCode"];
		$city = $_POST["city"];
		$country = $_POST["country"];
		$bank = $_POST["bank"];
		$bankAccount = $_POST["bankAccount"];
		$creditcard = $_POST["creditcard"];
		$phoneNumbers = $_POST["phone"];
		//Builds URL for signup-errors
		$addressRoot = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER["SERVER_NAME"] . "/sellerSignup/";
		$redirectAddress = $addressRoot;

		$this->data["phoneNumberCount"] = $dbh->query(
			"SELECT COUNT(*) AS amount FROM User_Phone WHERE [user] = :user",
			array(
				":user" => $_SESSION["username"]
			)
		);
		$this->data["firstId"] = $dbh->query("SELECT FIRST_VALUE(id) OVER (ORDER BY [user]) AS firstId FROM User_phone WHERE [user] = :user",
		array(
			":user" => $_SESSION["username"]
			)
		);

		if (!isset($firstname)) {
			$this->redirect($redirectAddress . "&signup-error=" . urlencode("Voornaam is niet ingevuld.")
			);
		} else if (!isset($lastName)) {
			$this->redirect($redirectAddress . "&signup-error=" . urlencode("Achternaam is niet ingevuld.")
			);
		} else if (!isset($userName)) {
			$this->redirect($redirectAddress . "&signup-error=" . urlencode("Gebruikersnaam is niet ingevuld.")
			);
		} else if (!isset($birthDate)) {
			$this->redirect($redirectAddress . "&signup-error=" . urlencode("Geboortedatum is niet ingevuld.")
			);
		} else if (!isset($adress1)) {
			$this->redirect($redirectAddress . "&signup-error=" . urlencode("Adres is niet ingevuld.")
			);
		} else if (!isset($postalCode)) {
			$this->redirect($redirectAddress . "&signup-error=" . urlencode("Postcode is niet ingevuld.")
			);
		} else if (!isset($city)) {
			$this->redirect($redirectAddress . "&signup-error=" . urlencode("Plaats is niet ingevuld.")
			);
		} else if (!isset($country)) {
			$this->redirect($redirectAddress . "&signup-error=" . urlencode("Land is niet ingevuld.")
			);
		}


		$dbh->query(
			<<<SQL
			UPDATE 	[User]
			SET first_name = :firstname, 
			last_name = :lastname, 
			address_line1 = :adress1, 
			address_line2 = :adress2, 
			zip_code = :zipcode,
			city = :city,
			country = :country,
			day_of_birth = :birthdate,
			mailbox = :email
			WHERE [username] = :username
			SQL,
			array(
				":username" => $_SESSION["username"],
				":firstname" => $_POST["firstName"], 
				":lastname" => $_POST["lastName"],
				":adress1" => $_POST["Adress1"],
				":adress2" => $_POST["Adress2"],
				":zipcode" => $_POST["PostalCode"],
				":city" => $_POST["city"],
				":country" => $_POST["country"],
				":birthdate" => $_POST["birthdate"],
				":email" => $_POST["E-mail"]

			)
		);

		for($i = 0; $i < $this->data["phoneNumberCount"][0]["amount"]; $i++) {
			$dbh->query(
				<<<SQL
				UPDATE 	User_phone
				SET phone = :phone
	
				WHERE [user] = :username AND id = :id
				SQL,
				array(
					":username" => $_SESSION["username"],
					":phone" => $phoneNumbers[$i],
					"id" => $this->data["firstId"][0]["firstId"]+$i
				)
			);
		}

		$this->redirect("/profile/");
	}
}
