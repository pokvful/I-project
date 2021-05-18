<?php

require_once $_SERVER["DOCUMENT_ROOT"] . '/src/database/databaseHandler.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/api/baseHandler.php';

class BiddingItemHandler extends BaseHandler {

	public function run() {
		if (isset($_POST["bid-button"])) {
			$dbh = new DatabaseHandler();

			$item = $_POST["item_number"];
			$bid_amount = $_POST["bid_amount"];
			$user = $_POST["username"];

			$dbh->query("UPDATE Bid SET item = :item, bid_amount = :bid_amount, [user] = :user, bid_day = DATE(), bid_time = TIME()", array(
				":item" => $item,
				":bid_amount" => $bid_amount,
				":user" => $user
			));

			$addressRoot = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER["SERVER_NAME"] . "/biddingItem/";
			$this->redirect("$addressRoot" . "?item_number=" . $item . "&user=" . $user . "?bid-error=" . urlencode("Bod is succesvol geplaatst."));
		}
	}
}