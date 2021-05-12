<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/controllers/baseController.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/database/databaseHandler.php";

class veilingItemController extends BaseController {
	public function run() {

		$dbh = new DatabaseHandler();

		$this->data["itemInformation"] = $dbh->query("SELECT item_number, title, [description], starting_price, payment_method, city, country, duration, duration_start_day, duration_start_time, shipping_cost, shipping_instructions, seller, duration_end_day, duration_end_time, selling_price FROM item");
		$this->render();
	}
}

