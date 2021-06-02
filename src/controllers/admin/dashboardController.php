<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/controllers/baseController.php";

class DashboardController extends BaseController {

	public function checkIfUserIsAdmin() {
		$dbh = new DatabaseHandler();
		$checkIfUserIsAdmin = $dbh->query("SELECT * FROM [User] WHERE username = :username AND [admin] = 1", array(

			":username" => $_SESSION["username"]

		));

		if (count($checkIfUserIsAdmin) <= 0) {
			$this->redirect("/");
		}
	}

	public function run() {
		$this->checkIfUserIsAdmin();
		$this->render();
	}
}
