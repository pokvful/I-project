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
		 * Initializes item_number and title variables
		 */
		$item_number = 1;
		$title = "bob";

		/**
		 * Selects title and item number from vw_Homepage view
		 */
//		$this->data["auctionItem"] = $dbh->query("SELECT :title FROM vw_Homepage WHERE item_number = :item_number", array(
//			":item_number" => $item_number,
//			":title" => $title,
//		));

		/**
		 * Selects all titles from item table
		 */
		$this->data["auctionItemTitles"] = $dbh->query("SELECT title FROM vw_Homepage");

		/**
		 * Selects all descriptions from item table
		 */
		$this->data["auctionItemDescriptions"] = $dbh->query("SELECT description FROM vw_Homepage");

		/**
		 * Outputs data to homepage
		 */
		$this->render();
	}
}
