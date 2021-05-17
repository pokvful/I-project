<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/database/databaseHandler.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/api/baseHandler.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/email.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/api/resetHandler.php';

class ResetHandler extends BaseHandler {
	//TODO: Code opschonen, checks uitvoeren voor wachtwoorden (wellicht nog wat andere foutmeldingen)?
	public function run() {
		$dbh = new DatabaseHandler();
		if (isset($_POST["Email"])) {
			$mailbox = $_POST["Email"];
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
			$questionAnswerQuery = $dbh->query("SELECT answer_text FROM [User] WHERE answer_text = :answer", array(
				"answer" => $_POST["questionAnswer"]
			));

			if (count($questionAnswerQuery) > 0) {
				$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
				$dbh->query("UPDATE [User] SET password = :passwordUser WHERE mailbox = :mailbox", array(
					":passwordUser" => $hashedPassword,
					":mailbox" => $mail
				));
			}

			$addressRoot = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER["SERVER_NAME"] . "/reset/";
			$this->redirect("$addressRoot" . "?reset-success=" . urlencode("Uw wachtwoord is succesvol gewijzigd.")
			);

		} else if (!isset($_POST["questionAnswer"]) && isset($_POST["password"])) {
			$security = $_POST["questionFromUser"];
			$mail = $_POST["mail"];
			$addressRoot = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER["SERVER_NAME"] . "/reset/";
			$redirectAddress = $addressRoot . "?question=" . "$security" . "&mail=" . "$mail";
			$this->redirect("$redirectAddress" . "&reset-error=" . urlencode("Onjuist wachtwoord ingevoerd.")
			);
		} else if (!isset($_POST["password"]) && isset($_POST["questionAnswer"])) {
			$security = $_POST["questionFromUser"];
			$mail = $_POST["mail"];
			$addressRoot = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER["SERVER_NAME"] . "/reset/";
			$redirectAddress = $addressRoot . "?question=" . "$security" . "&mail=" . "$mail";
			$this->redirect("$redirectAddress" . "&reset-error=" . urlencode("Onjuist beveiligingsvraag ingevoerd.")
			);
		}
	}

	function sendVerifyEmail($mail, $securityQuestion) {
		//Builds email that gets sent afterwards
		$emailBuilder = new Email("Reset password");
		$emailBuilder->addAddress($mail);
		$address = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER["SERVER_NAME"] . "/reset/";
		$emailBuilder->setText("Hey, <b>dit</b> is een test, je verificatielink is <a href=\"" . $address . "?question=" . $securityQuestion . "&mail=$mail\">Klik hier</a>");
		$emailBuilder->send();
		echo "Done :)";
	}
}



