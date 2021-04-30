<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/controllers/baseController.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/database/databaseHandler.php";

class HomeController extends BaseController {

	/**
	 * Runs homepage queries
	 */
	public function run() {
		/**
		 * Creates DatabaseHandler object
		 */
		$dbh = new DatabaseHandler();

		/**
		 * Selects title, item_number and description for vw_Homepage view
		 */
		$this->data["auctionItems"] = $dbh->query("SELECT title, item_number, [description] FROM vw_Homepage");

		/**
		 * Outputs data to homepage
		 */
		$this->render();
	}
}
