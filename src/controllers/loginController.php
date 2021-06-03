<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/controllers/baseController.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/database/databaseHandler.php';

class LoginController extends BaseController {
	public function run() {
		$this->data["signupSuccess"] = $_GET["signup-success"] ?? null;
		$this->data["resetSuccess"] = $_GET["reset-success"] ?? null;
		$this->data["loginError"] = $_GET["login-error"] ?? null;
		$this->data["username"] = $_GET["username"] ?? null;

		if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"]) {
			$this->redirect('/');
		}

		$this->render();
	}
}
