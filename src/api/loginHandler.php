<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/database/databaseHandler.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/api/baseHandler.php';

class LoginHandler extends BaseHandler {
	public function run() {
		if (!(isset($_POST["username"]) && $_POST["username"])) {
			$this->redirect("/login/?login-error=" . urlencode("Geen gebruikersnaam opgegeven!"));
		}

		if (!(isset($_POST["password"]) && $_POST["password"])) {
			$this->redirect(
				"/login/?login-error=" . urlencode("Geen wachtwoord opgegeven!")
					. "&username=" . urlencode($_POST["username"])
			);
		}

		$username = $_POST["username"];
		$password = $_POST["password"];

		$db = new DatabaseHandler();

		$users = $db->query(
			"SELECT [password], [admin], seller FROM [User] WHERE username = :username",
			array(
				":username" => $username,
			)
		);

		$userBlocked = $db->query(
			"SELECT blocked FROM [user] WHERE username = :username",
			array(
				":username" => $username,
			)
		);
		if (count($users) <= 0) {
			$this->redirect(
				"/login/?login-error=" . urlencode("Geen gebruiker met de gebruikersnaam \"$username\" gevonden.")
			);
		}

		if (!isset($userBlocked[0]["blocked"]) || $userBlocked[0]["blocked"]) {
			$this->redirect("/login/?login-error=" . urlencode("Dit account is geblokkeerd!"));
		}

		$user = $users[0];

		if (!password_verify($password, $user["password"])) {
			$this->redirect(
				"/login/?login-error=" . urlencode("Het wachtwoord is incorrect.")
					. "&username=" . urlencode($username)
			);
		}

		$_SESSION["loggedin"] = true;
		$_SESSION["username"] = $username;

		isset($_POST["redirect_uri"])
			? $this->redirect()
			: $this->redirect('/profile/');
	}
}
