<?php
require_once $_SERVER ["DOCUMENT_ROOT"] . '/src/baseController.php';


class SignupController extends BaseController {
	public function run () {
		$this->data["cameFromMail"] = false;
		$this->render();
	}
}