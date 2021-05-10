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
			$mailbox = $_POST["user"]; //uit de url
			$dateOfBirth = $_POST["birth_date"];
//		$phoneNumber = $_POST['phone-number-1"]; //Staat in andere tabel
			$address = $_POST["address"];
			$addressAddition = $_POST["address_addition"];
			$postalCode = $_POST["postal_code"];
			$city = $_POST["city"];
			$country = $_POST["country"];
			$question = $_POST["safety_question"]; //Verwijzing naar andere tabel
			$answerText = $_POST["question_answer"];

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

		$mailboxQuery = $dbh->query("SELECT * FROM [User] WHERE mailbox = :mailbox ", array(
			":mailbox" => $mail
		));

		$aantal = (count($mailboxQuery));
		//Checks if user email already exists in database
		if (count($mailboxQuery) > 0) {
			$this->redirect("/signup/?signup-error=" . urlencode("Dit e-mailadres is al in gebruik." . $aantal)
			);
		}

		//Filters values inside email input field
		if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
			$this->redirect("/signup/?signup-error=" . urlencode("Geen email opgegeven.")
			);
			$this->redirect(
				"/signup/?signup-error=" . urlencode("Geen geldige email opgegeven.")
				. "&email=" . urlencode($mail)
			);
		}

		//Initializes verificationLink variable with hashed value from current time and set email
		$verificationLink = password_hash(time() . $mail, PASSWORD_DEFAULT);

		//Builds email that gets sent afterwards
		$emailBuilder = new Email("Signup Email");
		$emailBuilder->addAddress($mail);
		$address = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER["SERVER_NAME"] . "/signup/";
		$emailBuilder->setText("Hey, <b>dit</b> is een test, je verificatielink is <a href=" . $address . "?hash=" . $verificationLink . "&user=$mail>Klik hier</a>");
		$emailBuilder->send();
		echo "Done :)";

		//Inserts email and verification code
		$dbh->query("INSERT INTO UserVerify (mailbox, verification_code) VALUES(:email, :verificationLink)", array(
			":email" => $mail,
			":verificationLink" => $verificationLink
		));
	}
}

