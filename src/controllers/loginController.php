<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/controllers/baseController.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/database/databaseHandler.php';

class LoginController extends BaseController {
	public function run() {
		$this->data["loginError"] = $_GET["login-error"] ?? null;
		$this->data["username"] = $_GET["username"] ?? null;

		// TODO: should work. needs to be tested first.
		// disallows access to login page, when logged in
		if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"]) {
			$this->redirect('/');
		}

		$this->render();
	}
}
