<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/database/databaseHandler.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/api/baseHandler.php';

class BlockHandler extends BaseHandler {
	public function run() {
		if ( !$_SESSION["admin"] )
			$this->redirect("/");

		if ( !isset( $_POST["remove_username"] ) )
			$this->redirect("/admin/users/");

		$username = $_POST["remove_username"];
		$db = new DatabaseHandler();

		$users = $db->query(
			"SELECT blocked FROM [User] WHERE username = :username",
			array(
				":username" => $username,
			),
		);

		if ( count($users) <= 0 )
			$this->redirect(
				"/admin/users/?error=" . urlencode(
					"De gebruikersnaam \"$username\" staat niet in de database."
				)
			);

		$blocked = intval( $users[0]["blocked"] );
		$newBlocked = $blocked ^ 1;

		try { 
			$db->query(
				<<<SQL
					UPDATE [User]
						SET blocked = :blocked
						WHERE username = :username
				SQL,
				array(
					":username" => $username,
					":blocked" => $newBlocked,
				)
			);
		} catch (Exception $e) {
			$this->redirect(
				"/admin/users/?error=" . urlencode(
					"Er ging iets mis tijdens het (de)blokkeren van een gebruiker: "
						. $e->getMessage()
				)
				);
		}

		$this->redirect(
			"/admin/users/?success=" . urlencode(
				"De gebruiker \"$username\" is succesvol " . ($newBlocked ? "geblokkeerd" : "gedeblokkeerd")
			)
		);
	}
}
