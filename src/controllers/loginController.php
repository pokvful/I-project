<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/controllers/baseController.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/database/databaseHandler.php';

class LoginController extends BaseController {
	public function run() {
		$this->data["loginError"] = $_GET["login-error"] ?? null;
		$this->data["username"] = $_GET["username"] ?? null;

		$this->render();
	}
}
