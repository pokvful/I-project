<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/controllers/baseController.php';

class LoginController extends BaseController {
	public function run() {
		$this->render();
	}
}
