<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/controllers/baseController.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/database/databaseHandler.php";

class BiddingItemsController extends BaseController {

	public function checkIfUserIsAdmin() {
		$dbh = new DatabaseHandler();
		$checkIfUserIsAdmin = $dbh->query("SELECT * FROM [User] WHERE username = :username AND [admin] = 1", array(

			":username" => $_SESSION["username"]

		));

		if (count($checkIfUserIsAdmin) <= 0) {
			$this->redirect("/");
		}
	}

	private function parseItems(array $items): array {
		foreach ($items as &$item) {
			$item["blocked"] = $item["blocked"] == 1;
		}
		return $items;
	}

	public function run() {
		$this->checkIfUserIsAdmin();

		if (!$this->data["_admin"] || !$this->data["_loggedin"])
			$this->redirect("/");

		$db = new DatabaseHandler();

		if (isset($_GET["title"]) && $_GET["title"] !== "") {
			$this->data["items"] = $this->parseItems(
				$db->query(
					<<<SQL
						SELECT title, item_number, blocked
							FROM Item
							WHERE title LIKE CONCAT('%', :title, '%')
							ORDER BY title
					SQL,
					array(
						":title" => $_GET["title"],
					)
				)
			);
		} else {
			$this->data["items"] = $this->parseItems(
				$db->query(
					"SELECT title, item_number, blocked FROM Item ORDER BY title"
				)
			);
		}

		$this->render();
	}
}
