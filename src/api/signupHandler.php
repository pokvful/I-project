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

	public function run() {
		$dbh = new DatabaseHandler();

		if (isset($_POST["hash"])) {
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
			$phoneNumber = $_POST["phone_number"];
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
			if (count($usernameQuery) > 0) {
				$this->redirect("$redirectAddress" . "&signup-error=" . urlencode("Deze gebruikersnaam is al in gebruik.")
				);
			} else if (strlen($unhashedPassword) < 8) {
				$this->redirect("$redirectAddress" . "&signup-error=" . urlencode("Het wachtwoord moet minimaal 8 karakters bevatten.")
				);
			} else if (!preg_match("#[0-9]+#", $unhashedPassword)) {
				$this->redirect("$redirectAddress" . "&signup-error=" . urlencode("Het wachtoord moet minimaal één cijfer bevatten.")
				);
			} else if (!preg_match("#[a-zA-Z]+#", $unhashedPassword)) {
				$this->redirect("$redirectAddress" . "&signup-error=" . urlencode("Het wachtoord moet minimaal één letter bevatten.")
				);
			} else if (strlen($phoneNumber) > 10 && !preg_match('/^[0-9-+]$/', $phoneNumber)) {
				$this->redirect("$redirectAddress" . "&signup-error=" . urlencode("Ongeldig telefoonnummer.")
				);
			} else if (count($mailboxQuery) > 0) {
				$this->redirect("/");
			} else if (strlen($postalCode) > 6) {
				$this->redirect("$redirectAddress" . "&signup-error=" . urlencode("Ongeldige postcode.")
				);
			}
			// TODO: Adding error messages for country and security question input field(s)

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
				));

			$dbh->query("INSERT INTO User_Phone ([user], phone) VALUES (:username, :phoneNumber)", array(
				":username" => $username,
				":phoneNumber" => $phoneNumber
			));

			$this->redirect("/login/?signup-success=" . urlencode("Uw account is succesvol aangemaakt.")
			);
		} else {
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
				$this->redirect("/signup/?signup-error=" . urlencode("Ongeldig e-mailadres.")
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
				$this->redirect("/signup/?signup-success=" . urlencode("Er is een verificatiecode verstuurd naar het e-mailadres: {$mail}")
				);
			} else if ($userVerifyTableCount == 0) {
				$dbh->query("INSERT INTO UserVerify(mailbox, verification_code) VALUES(:email, :verificationLink)", array(
					":email" => $mail,
					":verificationLink" => $verificationLink
				));
				$this->sendVerifyEmail($mail, $verificationLink);
				$this->redirect("/signup/?signup-success=" . urlencode("Er is een verificatiecode verstuurd naar het e-mailadres: {$mail}")
				);
			} else if ($userVerifyTableCount > 0 && $userTableQuery > 0) {
				$this->redirect("/signup/?signup-success=" . urlencode("U heeft al reeds een account aangemaakt met dit e-mailadres.")
				);
			}
//			else {
			// TODO: Make all input fields required (simplify this solution).
//				$mail = $_POST["user"];
//				$hash = $_POST["hash"];

			//Builds URL for signup-errors
//				$addressRoot = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER["SERVER_NAME"] . "/signup/";
//				$redirectAddress = $addressRoot . "?hash=" . "$hash" . "&user=" . "$mail";
//				$this->redirect("$redirectAddress" . "?signup-error=" . urlencode("Niet alle verplichte velden zijn ingevuld.")
//				);
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
		$emailBuilder->setText("Hey, <b>dit</b> is een test, je verificatielink is <a href=\"" . $address . "?hash=" . $verificationLink . "&user=$mail\">Klik hier</a>");
		$emailBuilder->send();
		echo "Done :)";
	}
}

