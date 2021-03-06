<?php

require_once $_SERVER["DOCUMENT_ROOT"] . '/src/database/databaseHandler.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/api/baseHandler.php';

class ChangeProfileHandler extends BaseHandler {
	public function run() {
		$dbh = new DatabaseHandler();

		$email = $_POST["email"];
		$firstName = $_POST["firstName"];
		$lastName = $_POST["lastName"];
		$birthDate = $_POST["birthdate"];
		$address1 = $_POST["address1"];
		$address2 = $_POST["address2"];
		$zipCode = $_POST["postalCode"];
		$city = $_POST["city"];
		$country = $_POST["country"];
		$bank = $_POST["bank"];
		$bankAccount = $_POST["bankAccount"];
		$addressRoot = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER["SERVER_NAME"] . "/changeProfile";
		if (isset($_POST["creditcard"])) {
			$creditcard = $_POST["creditcard"];
			if (!ctype_digit($creditcard)) {
				$this->redirect(
					$addressRoot . "?editProfile-error=" . urlencode("Ongeldige creditcardnummer.")
				);
			}
			$creditcardIsSet = true;
		} else {
			$creditcardIsSet = null;
		}
		$phoneNumbers = $_POST["phone"];

		//checks for space inputs
		if (ctype_space($email)) {
			$this->redirect($addressRoot . "?editProfile-error=" . urlencode("Ongeldige e-mail."));
		}
		if (ctype_space($firstName)) {
			$this->redirect($addressRoot . "?editProfile-error=" . urlencode("Ongeldige voornaam."));
		}
		if (ctype_space($lastName)) {
			$this->redirect($addressRoot . "?editProfile-error=" . urlencode("Ongeldige achternaam."));
		}
		if (ctype_space($birthDate)) {
			$this->redirect($addressRoot . "?editProfile-error=" . urlencode("Ongeldige geboortedatum."));
		}
		if (ctype_space($address1)) {
			$this->redirect($addressRoot . "?editProfile-error=" . urlencode("Ongeldige adres."));
		}
		if (ctype_space($address2)) {
			$this->redirect($addressRoot . "?editProfile-error=" . urlencode("Ongeldige adres."));
		}
		if (ctype_space($zipCode)) {
			$this->redirect($addressRoot . "?editProfile-error=" . urlencode("Ongeldige postcode."));
		}
		if (ctype_space($city)) {
			$this->redirect($addressRoot . "?editProfile-error=" . urlencode("Ongeldige stad."));
		}
		if (ctype_space($country)) {
			$this->redirect($addressRoot . "?editProfile-error=" . urlencode("Ongeldige land."));
		}
		if (ctype_space($bank)) {
			$this->redirect($addressRoot . "?editProfile-error=" . urlencode("Ongeldige bank."));
		}
		if (ctype_space($bankAccount)) {
			$this->redirect($addressRoot . "?editProfile-error=" . urlencode("Ongeldige rekeningnummer."));
		}


		//Builds URL for signup-errors
		$addressRoot = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER["SERVER_NAME"] . "/changeProfile";
		$this->data["editProfileError"] = $_GET["editProfile-error"] ?? null;
		$this->data["phoneNumberCount"] = $dbh->query(
			"SELECT COUNT(*) AS amount FROM User_Phone WHERE [user] = :user",
			array(
				":user" => $_SESSION["username"]
			)
		);
		$this->data["firstId"] = $dbh->query(
			"SELECT FIRST_VALUE(id) OVER (ORDER BY [user]) AS firstId FROM User_phone WHERE [user] = :user",
			array(
				":user" => $_SESSION["username"]
			)
		);

		if (strlen($zipCode) < 6) {
			$this->redirect(
				$addressRoot . "?editProfile-error=" . urlencode("Ongeldige postcode.")
			);
		}

		$dbh->query(
			<<<SQL
			UPDATE 	[User]
			SET first_name = :firstname, 
			last_name = :lastname, 
			address_line1 = :address1, 
			address_line2 = :address2, 
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
				":address1" => $_POST["address1"],
				":address2" => $_POST["address2"],
				":zipcode" => $_POST["postalCode"],
				":city" => $_POST["city"],
				":country" => $_POST["country"],
				":birthdate" => $_POST["birthdate"],
				":email" => $_POST["email"]

			)
		);

		for ($i = 0; $i < $this->data["phoneNumberCount"][0]["amount"]; $i++) {
			$dbh->query(
				<<<SQL
				UPDATE 	User_phone
				SET phone = :phone
				WHERE [user] = :username AND id = :id
				SQL,
				array(
					":username" => $_SESSION["username"],
					":phone" => $phoneNumbers[$i],
					"id" => $this->data["firstId"][0]["firstId"] + $i
				)
			);
		}
		if (isset($creditcardIsSet)) {
			$dbh->query(
				<<<SQL
			UPDATE 	[Seller]
			SET bank = :bank, 
			bank_account = :bankAccount, 
			creditcard = :creditcard 
			WHERE [user] = :username
			SQL,
				array(
					":username" => $_SESSION["username"],
					":bank" => $_POST["bank"],
					":bankAccount" => $_POST["bankAccount"],
					":creditcard" => $creditcard,
				)
			);
		} else {
			$dbh->query(
				<<<SQL
			UPDATE 	[Seller]
			SET bank = :bank, 
			bank_account = :bankAccount 
			WHERE [user] = :username
			SQL,
				array(
					":username" => $_SESSION["username"],
					":bank" => $_POST["bank"],
					":bankAccount" => $_POST["bankAccount"],
				)
			);
		}
		$this->redirect("/profile/");
	}
}
