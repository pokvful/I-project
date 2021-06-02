<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/src/controllers/baseController.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/database/databaseHandler.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/helpers/rubric.php";

class PlaceBiddingItemController extends BaseController {

	public function showPageToSeller() {
		if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
			$this->redirect("/");
		}
	}

	public function run() {
		$this->showPageToSeller();

		$dbh = new DatabaseHandler();
		$rubrics = RubricHelper::getRubricsFromDataBase();
		$this->data["rubrics"] = RubricHelper::getEndRubrics($rubrics);
		bdump($this->data["rubrics"]);

		$this->data["biddingSuccess"] = $_GET["bidding-success"] ?? null;
		$this->data["biddingError"] = $_GET["bidding-error"] ?? null;
		$seller = $_SESSION["username"];

		$getSellerLocationQuery = $dbh->query("
				SELECT city, country FROM [User] WHERE username = :username
				", array(
			":username" => $seller
		));

		$this->data["city"] = $getSellerLocationQuery[0]["city"] ?? null;
		$this->data["country"] = $getSellerLocationQuery[0]["country"] ?? null;
		$this->render();
	}
}
