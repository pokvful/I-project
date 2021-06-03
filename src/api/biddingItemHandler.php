<?php

require_once $_SERVER["DOCUMENT_ROOT"] . '/src/database/databaseHandler.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/api/baseHandler.php';

class BiddingItemHandler extends BaseHandler {
	public function run() {
		if (isset($_POST["bid_button"])) {
			$dbh = new DatabaseHandler();

			$item = $_POST["item_number"];
			$bid_amount = $_POST["bid_amount"];
			$user = $_SESSION["username"];

			$dbh->query(
				<<<SQL
					INSERT INTO Bid (item, bid_amount, [user], bid_day, bid_time)
					VALUES(:item, :bid_amount, :user, getdate(), CONVERT(TIME, GETDATE())) 
				SQL,
				array(
					":item" => $item,
					":bid_amount" => $bid_amount,
					":user" => $user
				)
			);

			$addressRoot = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER["SERVER_NAME"] . "/biddingItem/";
			$this->redirect("$addressRoot" . "?item_number=" . $item . "&bid-error=" . urlencode("Bod is succesvol geplaatst."));
		}
	}
}
