<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/views/baseView.php";

class HomeView extends BaseView {
	public function __construct(string $requestPath, string $path, string $fileName) {
		parent::__construct($requestPath, $path, $fileName);
	}

	public function run() {
		$this->render();
	}
}

