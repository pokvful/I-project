<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/controllers/baseController.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/database/databaseHandler.php";

class ProfileController extends BaseController {
	public function run() {
		$this->render();
	}
}