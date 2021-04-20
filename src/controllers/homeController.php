<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/controllers/baseController.php";

class HomeController extends BaseController {
	public function __construct(string $requestPath, string $filePath, string $fileName) {
		parent::__construct($requestPath, $filePath, $fileName);
	}

	public function run() {
		$this->render();
	}
}
