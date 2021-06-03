<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/controllers/baseController.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/database/databaseHandler.php";

class UsersController extends BaseController {

	public function checkIfUserIsAdmin() {
		$dbh = new DatabaseHandler();
		$checkIfUserIsAdmin = $dbh->query("SELECT * FROM [User] WHERE username = :username AND [admin] = 1", array(

			":username" => $_SESSION["username"]

		));

		if (count($checkIfUserIsAdmin) <= 0) {
			$this->redirect("/");
		}
	}

	private function parseUsers(array $users): array {
		foreach ($users as &$user) {
			$user["blocked"] = $user["blocked"] == 1;
			$user["admin"] = $user["admin"] == 1;
		}
		return $users;
	}

	public function run() {
		$this->checkIfUserIsAdmin();

		if (!$this->data["_admin"] || !$this->data["_loggedin"])
			$this->redirect("/");

		$db = new DatabaseHandler();

		if (isset($_GET["username"]) && $_GET["username"] !== "") {
			$this->data["users"] = $this->parseUsers(
				$db->query(
					<<<SQL
						SELECT username, blocked, [admin]
							FROM [User]
							WHERE username LIKE CONCAT('%', :username, '%')
							ORDER BY username
					SQL,
					array(
						":username" => $_GET["username"],
					)
				)
			);
		} else {
			$this->data["users"] = $this->parseUsers(
				$db->query(
					"SELECT username, blocked, [admin] FROM [User] ORDER BY username"
				)
			);
		}

		$this->render();
	}
}
