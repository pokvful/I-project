<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/controllers/baseController.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/database/databaseHandler.php";

class ProfileController extends BaseController {
	public function run() {

		$dbh = new DatabaseHandler();
		if(!isset($_GET["user"])) {
			$this->redirect("/");
		} else {
			$this->data["userInformation"] = $dbh->query("SELECT * FROM [user],User_phone WHERE username = :username", array(":username" => $_GET["user"]));

			$this->data["sellerInformation"] = $dbh->query("SELECT * FROM Seller WHERE [user] = :user", array(":user" => $_GET["user"]));
			if($this->data["userInformation"]["seller"] = 1) {
			}
		}

		$this->render();
	}
}
