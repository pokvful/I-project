<?php
require_once $_SERVER ["DOCUMENT_ROOT"] . '/src/controllers/baseController.php';
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/database/databaseHandler.php";
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/api/baseHandler.php';


class SellerSignupController extends BaseController {
	public function run() {
		$this->data["signupError"] = $_GET["signup-error"] ?? null;
		$this->render();
	}
}