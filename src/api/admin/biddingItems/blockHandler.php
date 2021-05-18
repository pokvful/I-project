<?php

require_once $_SERVER["DOCUMENT_ROOT"] . '/src/database/databaseHandler.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/api/baseHandler.php';

class BlockHandler extends BaseHandler {
	public function run() {
		if (!$_SESSION["admin"])
			$this->redirect("/");

		if (!isset($_POST["remove_title"]))
			$this->redirect("/admin/biddingItems/");

		$title = $_POST["remove_title"];
		$db = new DatabaseHandler();

		$titles = $db->query(
			"SELECT blocked FROM Item WHERE title = :title",
			array(
				":title" => $title,
			),
		);

		if (count($titles) <= 0)
			$this->redirect(
				"/admin/biddingItems/?error=" . urlencode(
					"Het veilingitem \"$title\" staat niet in de database."
				)
			);

		$blocked = intval($titles[0]["blocked"]);
		$newBlocked = $blocked ^ 1;

		try {
			$db->query(
				<<<SQL
					UPDATE Item
						SET blocked = :blocked
						WHERE title = :title
				SQL,
				array(
					":title" => $title,
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
				"Het veilingitem \"$title\" is succesvol " . ($newBlocked ? "geblokkeerd" : "gedeblokkeerd")
			)
		);
	}
}
