<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/controllers/baseController.php';
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/database/databaseHandler.php";
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/api/baseHandler.php';


class SellerSignupController extends BaseController {
	public function run() {
		if (!isset($_SESSION["loggedin"]) || !$_SESSION["loggedin"]) {
			$this->redirect("/");
		} else {
			$dbh = new DatabaseHandler();
			$this->data["sellerSignupError"] = $_GET["seller-signup-error"] ?? null;
			$this->data["isSeller"] = $dbh->query(
				"SELECT Seller FROM [User] WHERE [username] = :user",
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
}
