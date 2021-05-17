<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/database/databaseHandler.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/api/baseHandler.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/email.php';

class ResetHandler extends BaseHandler {
	public function run() {
		$dbh = new DatabaseHandler();
		/**
		 * Builds and sends email to users
		 *
		 * @param $mail [user] email
		 * @param $verificationLink ([user] specific) verification code
		 */

		function sendVerifyEmail($mail, $verificationLink) {
			//Builds email that gets sent afterwards
			$emailBuilder = new Email("Reset password");
			$emailBuilder->addAddress($mail);
			$address = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER["SERVER_NAME"] . "/signup/";
			$emailBuilder->setText("Klik op de onderstaande link om een nieuwe wachtwoord in te stellen.<br><a href=\"" . $address . "?hash=" . $verificationLink . "&user=$mail\">Nieuw wachtwoord instellen?</a>");
			$emailBuilder->send();
			echo "Done :)";
		}
	}
}
