<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/database/databaseHandler.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/api/baseHandler.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/email.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/api/resetHandler.php';

class ResetHandler extends BaseHandler {
	public function run() {
		$dbh = new DatabaseHandler();
		if (isset($_POST["email"])) {
			$mailbox = $_POST["email"];
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
				$addressRoot = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER["SERVER_NAME"] . "/reset/";
				$this->redirect("$addressRoot" . "?reset-success=" . urlencode("Er is een mail verstuurd, met daarin de desbetreffende link voor het resetten van uw wachtwoord.")
				);
			} else {
				$addressRoot = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER["SERVER_NAME"] . "/reset/";
				$this->redirect("$addressRoot" . "?reset-error=" . urlencode("Onjuiste gegevens.")
				);
			}
		}

		if (isset($_POST["questionAnswer"]) && isset($_POST["password"])) {
			$password = $_POST["password"];
			$mail = $_POST["mail"];
			$security = strtolower($_POST["questionFromUser"]);
			$addressRoot = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER["SERVER_NAME"] . "/reset/";
			$redirectAddress = $addressRoot . "?question=" . "$security" . "&mail=" . "$mail";
			$questionAnswerQuery = $dbh->query("SELECT answer_text FROM [User] WHERE answer_text = :answer AND mailbox = :mailbox", array(
				":answer" => $security,
				":mailbox" => $mail
			));

			if (strlen($password) < 8) {
				$this->redirect($redirectAddress . "&reset-error=" . urlencode("Het wachtwoord moet minimaal 8 karakters bevatten.")
				);
			} else if (!preg_match("#[0-9]+#", $password)) {
				$this->redirect($redirectAddress . "&reset-error=" . urlencode("Het wachtoord moet minimaal één cijfer bevatten.")
				);
			} else if (!preg_match("#[a-zA-Z]+#", $password)) {
				$this->redirect($redirectAddress . "&reset-error=" . urlencode("Het wachtoord moet minimaal één letter bevatten.")
				);
			} else if (!isset($_POST["questionAnswer"]) && isset($_POST["password"])) {
				$this->redirect("$redirectAddress" . "&reset-error=" . urlencode("Onjuist wachtwoord ingevoerd.")
				);
			} else if (!isset($_POST["password"]) && isset($_POST["questionAnswer"])) {
				$this->redirect("$redirectAddress" . "&reset-error=" . urlencode("Onjuist beveiligingsvraag ingevoerd.")
				);
			}

			if (count($questionAnswerQuery) > 0) {
				$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
				$dbh->query("UPDATE [User] SET password = :passwordUser WHERE mailbox = :mailbox", array(
					":passwordUser" => $hashedPassword,
					":mailbox" => $mail
				));

				$this->redirect("$addressRoot" . "?reset-success=" . urlencode("Uw wachtwoord is succesvol gewijzigd.")
				);
			} else {
				$this->redirect("$redirectAddress" . "&reset-error=" . urlencode("Onjuiste antwoord op beveiligingsvraag ingevoerd.")
				);
			}
		}
	}

	function sendVerifyEmail($mail, $securityQuestion) {
		//Builds email that gets sent afterwards
		$emailBuilder = new Email("Reset password");
		$emailBuilder->addAddress($mail);
		$address = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER["SERVER_NAME"] . "/reset/";
		$emailBuilder->setText("Beste gebruiker, <br> <br> U heeft aangevraagd om uw wachtwoord opnieuw in te stellen. <a href=\"" . $address . "?question=" . $securityQuestion . "&mail=$mail\"><br><br>Klik hier</a> om naar de pagina te gaan, waar u deze kunt instellen.");
		$emailBuilder->send();
		echo "Done :)";
	}
}



