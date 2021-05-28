<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/database/databaseHandler.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/api/baseHandler.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/email.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/api/resetHandler.php';

class ResetHandler extends BaseHandler {

	public function run() {

		$dbh = new DatabaseHandler();
		$addressRoot = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER["SERVER_NAME"] . "/reset/";

		if (isset($_POST["email"])) {
			$mailbox = $_POST["email"];
			if (!filter_var($mailbox, FILTER_VALIDATE_EMAIL)) {
				$this->redirect(
					"/reset/?reset-error=" . urlencode("Vul een geldig e-mailadres in.")
				);
			}

			$resetQuestionQuery = $dbh->query(
				"SELECT question_number, answer_text, q.text_question
						FROM [User] AS u
						INNER JOIN Question AS q
						ON u.question = q.question_number
						WHERE mailbox = :mailbox",
				array(
					":mailbox" => $mailbox
				)
			);

			if (count($resetQuestionQuery) > 0) {
				$securityQuestion = $resetQuestionQuery[0]["text_question"];
				$this->sendVerifyEmail($mailbox, $securityQuestion);
				$this->redirect(
					"$addressRoot" . "?reset-success=" . urlencode("Er is een mail verstuurd waarmee u uw wachtwoord opnieuw kunt instellen.")
				);
			} else {
				$this->redirect(
					"$addressRoot" . "?reset-error=" . urlencode("Onjuiste gegevens.")
				);
			}
		}

		if (isset($_POST["questionAnswer"]) && isset($_POST["password"])) {
			$mail = $_POST["mail"];
			$password = $_POST["password"];
			$security = $_POST["questionFromUser"];
			$answer = strtolower($_POST["questionAnswer"]);
			$redirectAddress = $addressRoot . "?question=" . "$security" . "&mail=" . "$mail";

			//Invalid password gets redirected
			if (strlen($password) < 8) {
				$this->redirect(
					"$redirectAddress" . "&reset-error=" . urlencode("Het wachtwoord moet minimaal 8 karakters bevatten.")
				);
			} else if (!preg_match("#[0-9]+#", $password)) {
				$this->redirect(
					"$redirectAddress" . "&reset-error=" . urlencode("Het wachtwoord moet minimaal 1 cijfer bevatten.")
				);
			} else if (!preg_match("#[a-zA-Z]+#", $password)) {
				$this->redirect(
					"$redirectAddress" . "&reset-error=" . urlencode("Het wachtwoord moet minimaal één letter bevatten.")
				);
			}

			//Compares the answer of the security question with the data inside the database
			$questionAnswerQuery = $dbh->query("SELECT answer_text FROM [User] WHERE answer_text = :answer AND mailbox = :mailbox", array(
				"answer" => $answer,
				"mailbox" => $mail
			));

			//Checks if the user has filled in the right answer and sends a success message
			if (count($questionAnswerQuery) > 0) {
				$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
				$dbh->query("UPDATE [User] SET password = :passwordUser WHERE mailbox = :mailbox", array(
					":passwordUser" => $hashedPassword,
					":mailbox" => $mail
				));
				$this->redirect(
					"$addressRoot" . "?reset-success=" . urlencode("Uw wachtwoord is succesvol gewijzigd.")
				);
			} else {
				$this->redirect(
					"$redirectAddress" . "&reset-error=" . urlencode("Beveiligingsvraag verkeerd beantwoord.")
				);
			}
		} else if (!isset($_POST["questionAnswer"]) && isset($_POST["password"])) {
			$security = $_POST["questionFromUser"];
			$mail = $_POST["mail"];
			$redirectAddress = $addressRoot . "?question=" . "$security" . "&mail=" . "$mail";
			$this->redirect(
				"$redirectAddress" . "&reset-error=" . urlencode("Onjuist wachtwoord ingevoerd.")
			);
		} else if (!isset($_POST["password"]) && isset($_POST["questionAnswer"])) {
			$security = $_POST["questionFromUser"];
			$mail = $_POST["mail"];
			$redirectAddress = $addressRoot . "?question=" . "$security" . "&mail=" . "$mail";
			$this->redirect(
				"$redirectAddress" . "&reset-error=" . urlencode("Onjuist antwoord beveiligingsvraag ingevoerd.")
			);
		}
	}

	function sendVerifyEmail($mail, $securityQuestion) {
		$emailBuilder = new Email("Reset password");
		$address = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER["SERVER_NAME"] . "/reset/";
		$emailBuilder->addAddress($mail);
		$emailBuilder->setText("Beste gebruiker, <br><br> U heeft aangevraagd om uw wachtwoord opnieuw in te stellen. <a href=\"" . $address . "?question=" . $securityQuestion . "&mail=$mail\"><br><br>Klik hier</a> om naar de pagina te gaan waar u deze kunt instellen.");
		$emailBuilder->send();
		echo "Done :)";
	}
}
