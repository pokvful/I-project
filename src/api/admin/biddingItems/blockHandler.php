<?php

require_once $_SERVER["DOCUMENT_ROOT"] . '/src/database/databaseHandler.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/api/baseHandler.php';

class BlockHandler extends BaseHandler {
	public function run() {
		if (!$_SESSION["admin"])
			$this->redirect("/");

		if (!isset($_POST["remove_item"]))
			$this->redirect("/admin/biddingItems/");

		$item_number = $_POST["remove_item"];
		$db = new DatabaseHandler();

		$item_numbers = $db->query(
			"SELECT blocked FROM Item WHERE item_number = :item_number",
			array(
				":item_number" => $item_number,
			),
		);

		if (count($item_numbers) <= 0)
			$this->redirect(
				"/admin/biddingItems/?error=" . urlencode(
					"Het veilingitem \"$item_number\" staat niet in de database."
				)
			);

		$blocked = intval($item_numbers[0]["blocked"]);
		$newBlocked = $blocked ^ 1;

		try {
			$db->query(
				<<<SQL
					UPDATE Item
						SET blocked = :blocked
						WHERE item_number = :item_number
				SQL,
				array(
					":item_number" => $item_number,
					":blocked" => $newBlocked,
				)
			);
		} catch (Exception $e) {
			$this->redirect(
				"/admin/biddingItems/?error=" . urlencode(
					"Er ging iets mis tijdens het (de)blokkeren van een item: "
						. $e->getMessage()
				)
			);
		}

		$this->redirect(
			"/admin/biddingItems/?success=" . urlencode(
				"Het veilingitem Nr.$item_number is succesvol " . ($newBlocked ? "geblokkeerd" : "gedeblokkeerd")
			)
		);
	}
}
