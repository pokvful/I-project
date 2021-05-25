<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/controllers/baseController.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/database/databaseHandler.php";

class HomeController extends BaseController {

	/**
	 * Runs homepage queries
	 */
	public function run() {

		$dbh = new DatabaseHandler();

		//Selects title, item_number and description from vw_Homepage view
		$this->data["auctionItems"] = $dbh->query("SELECT item_number, title, [description], [filename], bid_amount FROM vw_Homepage");

		$this->render();
	}
}
