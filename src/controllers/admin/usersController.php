<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/controllers/baseController.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/database/databaseHandler.php";

class UsersController extends BaseController {
	private function parseUsers(array $users): array {
		foreach ($users as &$user) {
			$user["blocked"] = $user["blocked"] == 1;
			$user["admin"] = $user["admin"] == 1;
		}

		return $users;
	}

	public function run() {
		$db = new DatabaseHandler();

		if ( isset( $_GET["username"] ) && $_GET["username"] !== "" ) { 
			$this->data["users"] = $this->parseUsers(
				$db->query(
					<<<SQL
						SELECT username, blocked, [admin]=1
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
					"SELECT username, blocked, [admin]=1 FROM [User] ORDER BY username"
				)
			);
		}

		$this->render();
	}
}
