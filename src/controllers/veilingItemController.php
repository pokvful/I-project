<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/controllers/baseController.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/database/databaseHandler.php";

class VeilingItemController extends BaseController {
	


	public function calculateMinimumBid(int $highestBid) {
		if($highestBid <1.00) {
			return 0;
		} else if($highestBid <50.00) {
			return 0.5;
		} else if($highestBid < 500.00) {
			return 1;
		} else if($highestBid < 1000.00) {
			return 5;
		} else if($highestBid < 5000.00) {
			return 10;
		} else {
			return 50;
		}
	}
	public function run() {

		$dbh = new DatabaseHandler();
		if(isset($_GET["item_number"])) {
			$this->data["itemInformation"] = $dbh->query("SELECT * FROM item WHERE item_number = :item_number", array(":item_number" => $_GET["item_number"]) );
			$this->data["bids"] = $dbh->query("SELECT item,bid_amount,[user],bid_day,LEFT(bid_time,8) AS bid_time FROM Bid WHERE item = :item_number", array(":item_number" => $_GET["item_number"]));
			$this->data["highestBid"] = $dbh->query("SELECT MAX(bid_amount) AS highestBid FROM Bid WHERE item = :item_number", array(":item_number" => $_GET["item_number"]));
			bdump($this->data["itemInformation"]);
			bdump($this->data["highestBid"]);
			if(!isset($this->data["highestBid"][0]["highestBid"])) {
				$this->data["minimumBid"] = $this->calculateMinimumBid($this->data["itemInformation"][0]["starting_price"]);
			} else {
				$this->data["minimumBid"] = $this->calculateMinimumBid($this->data["highestBid"][0]["highestBid"]);	
			}
		} else {
			$this->redirect("/items/");
		}

		$this->render();
	}



}

