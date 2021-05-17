<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/database/databaseHandler.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/api/baseHandler.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/email.php';

class ResetHandler extends BaseHandler
{
	public function run()
	{

		if (isset($_POST["email"])) {
			$email = $_POST["email"];

			$dbh = new DatabaseHandler();
			$hashQuery = $dbh->query("SELECT mailbox FROM [User] WHERE mailbox = :mailbox", array(
				":mailbox" => $email
			));
			$this->sendVerifyEmail($email);
		}

	}

	function sendVerifyEmail($mail)
	{
		//Builds email that gets sent afterwards
		$emailBuilder = new Email("Reset password");
		$emailBuilder->addAddress($mail);
		$address = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER["SERVER_NAME"] . "/reset/";
		$emailBuilder->setText("Hallo, dit is een test, klik op onderstaande link om je wachtwoord opnieuw in te stellen <a href=\"" . $address . "?mail=$mail\">Klik hier</a>");
		$emailBuilder->send();
		echo "Done :)";
	}
}

