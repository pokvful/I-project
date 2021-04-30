<?php
require_once $_SERVER ["DOCUMENT_ROOT"] . '/src/controllers/baseController.php';
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/database/databaseHandler.php";

/**
 * Class SignupController
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
		$queryOutput = $dbh->query("SELECT verification_code FROM Userverify WHERE mailbox = :email", array(
			":email" => $userEmail
		));

		//Checks if query is empty
		if (count($queryOutput) <= 0) {
			return;
		}

		//Checks if user hash corresponds with user email
		if ($userHash !== $queryOutput[0]["verification_code"]) {
			return;
		}

		$this->data['validVerification'] = true;
	}

	public function signupForm() {

	}

	public function run() {
		$this->data["signupError"] = $_GET["signup-error"] ?? null;
		$this->data["cameFromMail"] = false;
		$this->data["user"] = $_GET['user'] ?? null;

		$this->checkCredentials();
		$this->render();
	}
}