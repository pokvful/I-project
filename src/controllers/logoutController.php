<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/controllers/baseController.php";

class LogoutController extends BaseController {
	public function run() {
		unset( $_SESSION["username"] );
		$_SESSION["loggedin"] = false;

		$this->redirect("/"); // TODO: Or maybe to login page?
	}
}
