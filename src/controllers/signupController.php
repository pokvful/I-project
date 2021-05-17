<?php
require_once $_SERVER ["DOCUMENT_ROOT"] . '/src/controllers/baseController.php';
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/database/databaseHandler.php";

/**
 * Class SignupController
 *
 * Receives data from signup-page using GET requests
 */
class SignupController extends BaseController {

	public function checkCredentials() {
		$this->data['validVerification'] = false;

		if (!isset($_GET["user"]) || !isset($_GET["hash"]) || !$_GET["user"] || !$_GET["hash"]) {
			return;
		}

		$userEmail = $_GET["user"];
		$userHash = $_GET["hash"];
		$dbh = new DatabaseHandler();
		$time_clicked = date('Y-m-d H:i:s');

		$queryVerifyUser = $dbh->query("SELECT verification_code, mailbox, expiration_time FROM Userverify WHERE mailbox = :email", array(
			":email" => $userEmail
		));

		//Checks if query is empty
		if (count($queryVerifyUser) <= 0) {
			return;
		}

		//Checks if user hash corresponds with user email
		if ($userHash !== $queryVerifyUser[0]["verification_code"]) {
			$this->redirect("/signup/?signup-error=" . urlencode("Verificatiegegevens zijn onjuist, probeer het later opnieuw.")
			);
		}

		//Checks if verification code hasn't been expired
		if ($time_clicked > $queryVerifyUser[0]["expiration_time"]) {
			$this->redirect("/signup/?signup-error=" . urlencode("De verificatiecode is vervallen.")
			);
		}

		$this->data['validVerification'] = true;
	}

	public function run() {
		$this->data["signupError"] = $_GET["signup-error"] ?? null;
		$this->data["signupSuccess"] = $_GET["signup-success"] ?? null;
		$this->data["user"] = $_GET['user'] ?? null;
		$this->data["hash"] = $_GET['hash'] ?? null;

		$this->checkCredentials();
		$this->render();
	}
}