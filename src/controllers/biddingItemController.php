<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/controllers/baseController.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/database/databaseHandler.php";

class BiddingItemController extends BaseController {
	public function calculateMinimumBid(float $highestBid) {
		if ($highestBid < 1.00) {
			return 0.00;
		} else if ($highestBid < 50.00) {
			return 0.50;
		} else if ($highestBid < 500.00) {
			return 1.00;
		} else if ($highestBid < 1000.00) {
			return 5.00;
		} else if ($highestBid < 5000.00) {
			return 10.00;
		} else {
			return 50.00;
		}
	}

	public function run() {
		$dbh = new DatabaseHandler();

		//Builds URL for signup-errors
		$addressRoot = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER["SERVER_NAME"] . "/items/";

		if (!isset($_GET["item_number"])) {
			$this->redirect("/items/");
		} else {
			$this->data["itemInformation"] = $dbh->query(
				"SELECT * FROM item WHERE item_number = :item_number",
				array(
					":item_number" => $_GET["item_number"]
				)
			);

			$userblocked = $dbh->query(
				"SELECT blocked FROM [user] WHERE username = :username",
				array(
					":username" => $this->data["itemInformation"][0]["seller"]
				)
			);
			
			if($this->data["itemInformation"][0]["blocked"] == 1 || $userblocked[0]["blocked"] == 1) {
				$this->redirect($addressRoot . "?item-error=" . urlencode("Deze veiling is geblokkeerd"));
			}
			

			$this->data["startingTime"] = $dbh->query(
				"SELECT LEFT(duration_start_time,8) AS starting_time FROM item WHERE item_number = :item_number",
				array(
					":item_number" => $_GET["item_number"]
				)
			);
			$this->data["bids"] = $dbh->query(
				<<<SQL
				SELECT item, bid_amount, [user], bid_day, LEFT(bid_time,8) AS bid_time FROM Bid 
				WHERE item = :item_number
				ORDER BY bid_day DESC, bid_time DESC
				SQL,
				array(
					":item_number" => $_GET["item_number"]
				)
			);
			$this->data["highestBid"] = $dbh->query(
				"SELECT MAX(bid_amount) AS highestBid FROM Bid WHERE item = :item_number",
				array(
					":item_number" => $_GET["item_number"]
				)
			);

			$this->data["images"] = $dbh->query("SELECT [filename] FROM [File] WHERE item = :item_number", array(

				":item_number" => $_GET["item_number"]

			));

			$minimumBid = 0;

			if (!isset($this->data["highestBid"][0]["highestBid"])) {
				$minimumBid = $this->calculateMinimumBid($this->data["itemInformation"][0]["starting_price"]);
			} else {
				$minimumBid = $this->calculateMinimumBid($this->data["highestBid"][0]["highestBid"]);
			}

			if (is_null($this->data["highestBid"][0]["highestBid"])) {
				$this->data["minimumBid"] = $this->data["itemInformation"][0]["starting_price"];
			} else {
				$this->data["minimumBid"] = $this->data["highestBid"][0]["highestBid"] + $minimumBid;
			}

			$this->data["bidError"] = $_GET["bid-error"] ?? null;
			$this->data["bidSuccess"] = $_GET["bid-success"] ?? null;
			$this->data["item_number"] = $_GET['item_number'] ?? null;

			$this->render();
		}
	}
}
