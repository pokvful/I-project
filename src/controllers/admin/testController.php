<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/controllers/baseController.php";

class TestController extends BaseController {
	public function run() {
		$this->render();
	}
}
