<?php

require_once $_SERVER["DOCUMENT_ROOT"] . '/src/controllers/baseController.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/database/databaseHandler.php';

class myItemsController extends BaseController {

	public function checkUserItems() {

		// Checks if the user is loggedin
		if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
			$this->redirect('/login/?redirect_uri=' . urlencode("/myItems/"));
		}
		$dbh = new DatabaseHandler();
		// Selects all items a user has a bid on
		$this->data["userMyItems"] = $dbh->query(
			"SELECT * FROM vw_MyItems WHERE [user] = :user",
			array(
				":user" => $_SESSION["username"]
			)
		);

		if (count($this->data["userMyItems"]) <= 0 && !isset($_GET["myItems-error"])) {
			$this->redirect(
				"/myItems/?myItems-error=" . urlencode("Er zijn nog geen items waarop u geboden heeft.")
			);
		}

		// Checks if the user is a seller
		if (isset($_SESSION["seller"]) && $_SESSION["seller"]) {
			// Selects the items the seller sells
			$this->data["sellerMyItems"] = $dbh->query(
				"SELECT * FROM vw_MySellerItems WHERE seller = :seller",
				array(
					":seller" => $_SESSION["username"]
				)
			);

			if (count($this->data["sellerMyItems"]) <= 0 && !isset($_GET["mySellerItems-error"])) {
				$this->redirect(
					"/myItems/?mySellerItems-error=" . urlencode("Er zijn geen items die u aanbiedt.")
				);
			}
		}
	}

	public function run() {
		$this->checkUserItems();
		$this->data["myItemsError"] = $_GET["myItems-error"] ?? null;
		$this->data["mySellerItems"] = $_GET["mySellerItems-error"] ?? null;
		$this->render();
	}
}
