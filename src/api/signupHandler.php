<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/database/databaseHandler.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/api/baseHandler.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/email.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/api/signupHandler.php';

/**
 * Class SignupHandler
 *
 * Receives data from signup-page using POST requests
 */
class SignupHandler extends BaseHandler {

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

		if (isset($_POST["hash"])) { // user came from verification email
			$firstname = $_POST["first_name"];
			$lastname = $_POST["last_name"];
			$username = $_POST["username"];
			$unhashedPassword = $_POST["password"];
			$hashedPassword = password_hash($unhashedPassword, PASSWORD_DEFAULT);
			$mailbox = $_POST["user"];
			$dateOfBirth = $_POST["birth_date"];
			$address = $_POST["address"];
			$addressAddition = $_POST["address_addition"];
			$postalCode = $_POST["postal_code"];
			$city = $_POST["city"];
			$country = $_POST["country"];
			$question = $_POST["safety_question"];
			$answerText = $_POST["question_answer"];
			$phoneNumbers = $_POST["phone_number"];
			$hash = $_POST["hash"];

			//SQL queries for username, verification_code and e-mail
			$usernameQuery = $dbh->query("SELECT username FROM [User] WHERE username = :username", array(
				":username" => $username
			));
			$mailboxQuery = $dbh->query("SELECT mailbox FROM [User] WHERE mailbox = :mailbox", array(
				":mailbox" => $mailbox
			));

			//Builds URL for signup-errors
			$addressRoot = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER["SERVER_NAME"] . "/signup/";
			$redirectAddress = $addressRoot . "?hash=" . "$hash" . "&user=" . "$mailbox";

			//Filters form inputs
			if (!isset($firstname)) {
				$this->redirect(
					$redirectAddress . "&signup-error=" . urlencode("Voornaam is niet ingevuld.")
				);
			} else if (!isset($lastname)) {
				$this->redirect(
					$redirectAddress . "&signup-error=" . urlencode("Achternaam is niet ingevuld.")
				);
			} else if (!isset($username)) {
				$this->redirect(
					$redirectAddress . "&signup-error=" . urlencode("Gebruikersnaam is niet ingevuld.")
				);
			} else if (!isset($unhashedPassword)) {
				$this->redirect(
					$redirectAddress . "&signup-error=" . urlencode("Wachtwoord is niet ingevuld.")
				);
			} else if (!isset($dateOfBirth)) {
				$this->redirect(
					$redirectAddress . "&signup-error=" . urlencode("Geboortedatum is niet ingevuld.")
				);
			} else if (!isset($phoneNumbers)) {
				$this->redirect(
					$redirectAddress . "&signup-error=" . urlencode("Telefoonnummer is niet ingevuld.")
				);
			} else if (!isset($address)) {
				$this->redirect(
					$redirectAddress . "&signup-error=" . urlencode("Adres is niet ingevuld.")
				);
			} else if (!isset($postalCode)) {
				$this->redirect(
					$redirectAddress . "&signup-error=" . urlencode("Postcode is niet ingevuld.")
				);
			} else if (!isset($city)) {
				$this->redirect(
					$redirectAddress . "&signup-error=" . urlencode("Plaats is niet ingevuld.")
				);
			} else if (!isset($country)) {
				$this->redirect(
					$redirectAddress . "&signup-error=" . urlencode("Land is niet ingevuld.")
				);
			} else if (!isset($question)) {
				$this->redirect(
					$redirectAddress . "&signup-error=" . urlencode("Antwoord beveiligingsvraag is niet ingevuld.")
				);
			} else if (!isset($answerText)) {
				$this->redirect(
					$redirectAddress . "&signup-error=" . urlencode("Antwoord beveiligingsvraag is niet ingevuld.")
				);
			} else if (count($usernameQuery) > 0) {
				$this->redirect(
					$redirectAddress . "&signup-error=" . urlencode("Deze gebruikersnaam is al in gebruik.")
				);
			} else if (strlen($unhashedPassword) < 8) {
				$this->redirect(
					$redirectAddress . "&signup-error=" . urlencode("Het wachtwoord moet minimaal 8 karakters bevatten.")
				);
			} else if (!preg_match("#[0-9]+#", $unhashedPassword)) {
				$this->redirect(
					$redirectAddress . "&signup-error=" . urlencode("Het wachtoord moet minimaal één cijfer bevatten.")
				);
			} else if (!preg_match("#[a-zA-Z]+#", $unhashedPassword)) {
				$this->redirect(
					$redirectAddress . "&signup-error=" . urlencode("Het wachtoord moet minimaal één letter bevatten.")
				);
			} else if (count($mailboxQuery) > 0) {
				$this->redirect("/");
			} else if (strlen($postalCode) > 6) {
				$this->redirect(
					$redirectAddress . "&signup-error=" . urlencode("Ongeldige postcode.")
				);
			}

			foreach ($phoneNumbers as $phoneNumber) {
				if (strlen($phoneNumber) > 10 && !preg_match('/^[0-9-+]$/', $phoneNumber)) {
					$this->redirect(
						$redirectAddress . "&signup-error=" . urlencode("Ongeldig telefoonnummer.")
					);
				}
			}

			//Inserts user-filled data into database
			$dbh->query(
				<<<SQL
					INSERT INTO [User] (username, mailbox, first_name,  last_name, address_line1, 
									address_line2, zip_code, city, country, day_of_birth, [password], 
									question, answer_text, seller)
									VALUES (:username, :mailbox ,:firstname, :lastname, :address, :addressAddition, :postalCode, 
									:city, :country, :dateOfBirth, :password, :question, :answerText, 0)
				SQL,
				array(
					":username" => $username,
					":mailbox" => $mailbox,
					":firstname" => $firstname,
					":lastname" => $lastname,
					":address" => $address,
					":addressAddition" => $addressAddition,
					":postalCode" => $postalCode,
					":city" => $city,
					":country" => $country,
					":dateOfBirth" => $dateOfBirth,
					":password" => $hashedPassword,
					":question" => $question,
					":answerText" => $answerText
				)
			);

			$getUserLocations = $dbh->query("SELECT city, country FROM [User] WHERE username = :username", array(
				":username" => $username
			));

			$address = $getUserLocations[0]["city"] . ' ' . $getUserLocations[0]["country"];
			$address = str_replace(' ', '+', $address);

			$longitude = substr($this->getGeoCode($address), 0, strpos($this->getGeoCode($address), '+'));
			$latitude = substr($this->getGeoCode($address), strlen($longitude) + 1);

			$updateLatLongQuery = $dbh->query("UPDATE [User] SET latitude = :latitude, longitude = :longitude WHERE username = :username", array(

				":latitude" => $latitude,
				":longitude" => $longitude,
				":username" => $username

			));

			// TODO: This isn't the most optimal solution
			foreach ($phoneNumbers as $phoneNumber) {
				$dbh->query("INSERT INTO User_Phone ([user], phone) VALUES (:username, :phoneNumber)", array(
					":username" => $username,
					":phoneNumber" => $phoneNumber
				));
			}

			$dbh->query("DELETE FROM Userverify WHERE mailbox = :mailbox", array(
				":mailbox" => $mailbox
			));

			$this->redirect(
				"/login/?signup-success=" . urlencode("Uw account is succesvol aangemaakt.")
			);
		} else { // user didn't come from verification email
			$mail = $_POST["user"];

			//SQL queries for email data from verified and unverified user account
			$userVerifyQuery = $dbh->query("SELECT mailbox FROM UserVerify WHERE mailbox = :mailbox", array(
				":mailbox" => $mail
			));
			$userTableQuery = $dbh->query("SELECT mailbox FROM [User] WHERE mailbox = :mailbox", array(
				":mailbox" => $mail
			));

			//Counts query results
			$userVerifyTableCount = (count($userVerifyQuery));
			$userTableQuery = (count($userTableQuery));

			//Checks if e-mail already exists in the database
			if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
				$this->redirect(
					"/signup/?signup-error=" . urlencode("Ongeldig e-mailadres.")
				);
			}

			//Initializes verificationLink variable with hashed value from current time and set email
			$verificationLink = password_hash(time() . $mail, PASSWORD_DEFAULT);

			if ($userVerifyTableCount > 0 && $userTableQuery <= 0) {
				$dbh->query("UPDATE Userverify SET expiration_time = DATEADD(HOUR, 4, GETDATE()) WHERE mailbox = :mailbox", array(
					":mailbox" => $mail
				));
				$dbh->query("UPDATE Userverify SET verification_code = :verification_link WHERE mailbox = :mailbox", array(
					":mailbox" => $mail,
					":verification_link" => $verificationLink
				));
				$this->sendVerifyEmail($mail, $verificationLink);
				$this->redirect(
					"/signup/?signup-success=" . urlencode("Er is een verificatiecode verstuurd naar het e-mailadres: {$mail}")
				);
			} else if ($userVerifyTableCount == 0) {
				$dbh->query("INSERT INTO UserVerify (mailbox, verification_code) VALUES (:email, :verificationLink)", array(
					":email" => $mail,
					":verificationLink" => $verificationLink
				));
				$this->sendVerifyEmail($mail, $verificationLink);
				$this->redirect(
					"/signup/?signup-success=" . urlencode("Er is een verificatiecode verstuurd naar het e-mailadres: {$mail}")
				);
			} else if ($userVerifyTableCount > 0 && $userTableQuery > 0) {
				$this->redirect(
					"/signup/?signup-success=" . urlencode("U heeft al reeds een account aangemaakt met dit e-mailadres.")
				);
			}
		}
	}


	/**
	 * Builds and sends email to users
	 *
	 * @param $mail [user] email
	 * @param $verificationLink ([user] specific) verification code
	 */
	public
	function sendVerifyEmail($mail, $verificationLink) {
		//Builds email that gets sent afterwards
		$emailBuilder = new Email("Signup Email");
		$emailBuilder->addAddress($mail);
		$address = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER["SERVER_NAME"] . "/signup/";
		$emailBuilder->setText("Leuk dat je hebt gekozen voor <b>EenmaalAndermaal!</b> Je verificatielink is <a href=\"" . $address . "?hash=" . $verificationLink . "&user=$mail\">Klik hier</a>");
		$emailBuilder->send();
		echo "Done :)";
	}
}
