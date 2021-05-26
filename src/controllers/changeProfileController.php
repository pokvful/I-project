<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/controllers/baseController.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/database/databaseHandler.php';

class ChangeProfileController extends BaseController {
	public function run() {
		$dbh = new DatabaseHandler();

		if (!isset($_SESSION["loggedin"]) || !$_SESSION["loggedin"]) {
			$this->redirect("/");
		} else {
			$this->data["signupError"] = $_GET["signup-error"] ?? null;
			$this->data["userInformation"] = $dbh->query(
				"SELECT * FROM [User] WHERE username = :username",
				array(
					":username" => $_SESSION["username"]
				)
			);
			$this->data["sellerInformation"] = $dbh->query(
				"SELECT * FROM Seller WHERE [user] = :user",
				array(
					":user" => $_SESSION["username"]
				)
			);
			$this->data["phoneNumber"] = $dbh->query(
				"SELECT * FROM User_Phone WHERE [user] = :user",
				array(
					":user" => $_SESSION["username"]
				)
			);
			$this->data["phoneNumberCount"] = $dbh->query(
				"SELECT COUNT(*) AS amount FROM User_Phone WHERE [user] = :user",
				array(
					":user" => $_SESSION["username"]
				)
			);
		}
		$this->render();
	}
}
