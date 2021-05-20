<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/src/controllers/baseController.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/database/databaseHandler.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/helpers/rubric.php";

class PlaceBiddingItemController extends BaseController {

	public function loadRubrics() {
		$dbh = new DatabaseHandler();
		$this->data['categories'] = $dbh->query("SELECT rubric_number, rubric_name from Rubric");
		bdump($this->data['categories']);
	}

	public function run() {
		$this->loadRubrics();
		$rubrics = RubricHelper::getRubricsFromDataBase();
		bdump($rubrics);

		$this->data["biddingSuccess"] = $_GET["bidding-success"] ?? null;
		$this->data["biddingError"] = $_GET["bidding-error"] ?? null;

		$this->render();
	}
}