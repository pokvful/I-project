<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/src/controllers/baseController.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/database/databaseHandler.php";

class PlaceBiddingItemController extends BaseController {

	public function run(){

		$this->data["biddingError"] = $_GET["bidding-error"] ?? null;
		$this->data["biddingSuccess"] = $_GET["bidding-success"] ?? null;

		$this->render();
	}


}