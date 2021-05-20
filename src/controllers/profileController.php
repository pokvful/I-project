<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/controllers/baseController.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/database/databaseHandler.php";

class ProfileController extends BaseController {
	public function run() {
		$dbh = new DatabaseHandler();

		if (!isset($_SESSION["loggedin"])) {
			$this->redirect("/login/?redirect_uri=" . urlencode("/profile/"));
		} else {
			$this->data["userInformation"] = $dbh->query(
				"SELECT * FROM [user],User_phone WHERE username = :username",
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
