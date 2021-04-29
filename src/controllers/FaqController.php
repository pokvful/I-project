<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/controllers/baseController.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/database/databaseHandler.php";

class FaqController extends BaseController {
	public function run() {
		$db = new DatabaseHandler();

		if ( !isset( $_GET["questionId"] ) ) {
			$this->data["items"] = $db->query("SELECT id, questionText FROM Faq");
		} else {
			$this->data["questionItem"] = $db->query(
				"SELECT questionText, answerText FROM Faq WHERE id = :id",
				array(
					":id" => $_GET["questionId"]
				)
			);
		}

		$this->data["hasAnswer"] = isset($this->data["questionItem"]) && count( $this->data["questionItem"] ) > 0;
		
		$this->render();
	}
}