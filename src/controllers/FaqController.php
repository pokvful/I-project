<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/controllers/baseController.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/database/databaseHandler.php";

class FaqController extends BaseController {
	public function run() {
		$db = new DatabaseHandler();

		//Makes sure you only load the necessary content from the database. if you are on the 'hub' Faq page, you don't need the answer.
		if (!isset($_GET["questionId"])) {
			$this->data["items"] = $db->query("SELECT id, questionText FROM Faq");
		} else {
			$this->data["questionItem"] = $db->query(
				"SELECT questionText, answerText FROM Faq WHERE id = :id",
				array(
					":id" => $_GET["questionId"]
				)
			);
		}

		//Checks if you are looking at the question page, instead of the hub page.
		$this->data["hasAnswer"] = isset($this->data["questionItem"]) && count($this->data["questionItem"]) > 0;

		$this->render();
	}
}
