<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/controllers/baseController.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/database/databaseHandler.php";

class BiddingItemController extends BaseController {
	public function calculateMinimumBid(int $highestBid) {
		if ($highestBid < 1.00) {
			return 0;
		} else if ($highestBid < 50.00) {
			return 0.5;
		} else if ($highestBid < 500.00) {
			return 1;
		} else if ($highestBid < 1000.00) {
			return 5;
		} else if ($highestBid < 5000.00) {
			return 10;
		} else {
			return 50;
		}
	}

	public function run() {
		$dbh = new DatabaseHandler();

		if (!isset($_GET["item_number"])) {
			$this->redirect("/items/");
		} else {
			$this->data["itemInformation"] = $dbh->query(
				"SELECT * FROM item WHERE item_number = :item_number",
				array(
					":item_number" => $_GET["item_number"]
				)
			);
			$this->data["bids"] = $dbh->query("
				SELECT item, bid_amount, [user], bid_day, LEFT(bid_time,8) AS bid_time FROM Bid 
				WHERE item = :item_number
				ORDER BY bid_day DESC, bid_time DESC"
				, array(
					":item_number" => $_GET["item_number"]
				)
			);
			$this->data["highestBid"] = $dbh->query(
				"SELECT MAX(bid_amount) AS highestBid FROM Bid WHERE item = :item_number",
				array(
					":item_number" => $_GET["item_number"]
				)
			);

			if (!isset($this->data["highestBid"][0]["highestBid"])) {
				$this->data["minimumBid"] = $this->calculateMinimumBid($this->data["itemInformation"][0]["starting_price"]);
			} else {
				$this->data["minimumBid"] = $this->calculateMinimumBid($this->data["highestBid"][0]["highestBid"]);
			}

			$this->data["bidError"] = $_GET["bid-error"] ?? null;
			$this->data["bidSuccess"] = $_GET["bid-success"] ?? null;
			$this->data["item_number"] = $_GET['item_number'] ?? null;
			$this->render();
		}
	}
}
