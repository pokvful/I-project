<?php
require_once $_SERVER ["DOCUMENT_ROOT"] . '/src/controllers/baseController.php';


class SignupController extends BaseController {
	public function run () {
		$this->data["cameFromMail"] = true;
		$this->data["userData"] = array(
			"email" => "henk@example.com",
		);

		$this->render();
	}
}