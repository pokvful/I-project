<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/database/databaseHandler.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/api/baseHandler.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/email.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/api/signupHandler.php';

/**
 * Class SignupHandler
 */
class SignupHandler extends BaseHandler {

	public function run() {
		$dbh = new DatabaseHandler();

		if (isset($_POST['first_name'])) {
			$firstname = $_POST["first_name"];
			$lastname = $_POST["last_name"];
			$username = $_POST["username"];
			$password = password_hash($_POST["password"], PASSWORD_DEFAULT);
			$mailbox = $_POST["user"];
			$dateOfBirth = $_POST["birth_date"];
			$address = $_POST["address"];
			$addressAddition = $_POST["address_addition"];
			$postalCode = $_POST["postal_code"];
			$city = $_POST["city"];
			$country = $_POST["country"];
			$question = $_POST["safety_question"];
			$answerText = $_POST["question_answer"];

			$usernameQuery = $dbh->query("SELECT username FROM [User] WHERE username = :username", array(
				":username" => $username
			));

			if (count($usernameQuery) > 0) {
				$this->redirect("/home/?signup-success=" . urlencode("Er is een verificatiecode verstuurd naar het e-mailadres:")
				);
			}

			if (strlen($password) < 8) {
				$this->redirect("/signup/?signup-error=" . urlencode("Het wachtwoord moet minimaal 8 karakters bevatten.")
				);
			}
			if (!preg_match("#[0-9]+#", $password)) {
				$this->redirect("/signup/?signup-error=" . urlencode("Het wachtoord moet minimaal één cijfer bevatten.")
				);
			}
			if (!preg_match("#[a-zA-Z]+#", $password)) {
				$this->redirect("/signup/?signup-error=" . urlencode("Het wachtoord moet minimaal één letter bevatten.")
				);
			}

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
					":password" => $password,
					":question" => $question,
					":answerText" => $answerText
				));
		}

		$mail = $_POST["email"];

		$userVerifyQuery = $dbh->query("SELECT mailbox FROM UserVerify WHERE mailbox = :mailbox ", array(
			":mailbox" => $mail
		));

		$userTableQuery = $dbh->query("SELECT mailbox FROM [User] WHERE mailbox = :mailbox ", array(
			":mailbox" => $mail
		));


		//User van geverifieerde Users tabel
		$userVerifyTableCount = (count($userVerifyQuery));
		$userTableQuery = (count($userTableQuery));

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
		} else {
			$this->redirect("/signup/?signup-error=" . urlencode("Dit e-mailadres is al in gebruik")
			);
		}

		//Filters values inside email input field
		if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
			$this->redirect(
				"/signup/?signup-error=" . urlencode("Geen geldige email opgegeven.")
				. " & email = " . urlencode($mail)
			);
		}
	}

	public function sendVerifyEmail($mail, $verificationLink) {
		//Builds email that gets sent afterwards
		$emailBuilder = new Email("Signup Email");
		$emailBuilder->addAddress($mail);
		$address = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER["SERVER_NAME"] . "/signup/";
		$emailBuilder->setText("Hey, <b>dit</b> is een test, je verificatielink is <a href=\"" . $address . "?hash=" . $verificationLink . "&user=$mail\">Klik hier</a>");
		$emailBuilder->send();
		echo "Done :)";
	}
}
