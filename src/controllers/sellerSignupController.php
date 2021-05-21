<?php
require_once $_SERVER ["DOCUMENT_ROOT"] . '/src/controllers/baseController.php';
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/database/databaseHandler.php";
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/api/baseHandler.php';


class SellerSignupController extends BaseController {
	public function run() {
		$dbh = new DatabaseHandler();
		$this->data["signupError"] = $_GET["signup-error"] ?? null;
		$this->data["isSeller"] = $dbh->query("SELECT Seller FROM [User] WHERE [username] = :user",
			array(
				":user" => $_SESSION["username"]
			)
		);
		if ($this->data["isSeller"][0]["Seller"] == 1) {
			$this->redirect("/");
		} else {
			$this->render();
		}
	}
}