<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/controllers/baseController.php";

class NotFoundController extends BaseController {
	public function run() {
		$this->data["paths"] = explode('/', $this->data["_requestPath"]);

		http_response_code(404);

		$this->render();
	}
}
