<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/controllers/baseController.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/database/databaseHandler.php';

class ResetController extends BaseController {

	public function checkCredentials() {
		$this->data['eligibleForReset'] = false;

		if (isset($_GET["mail"])) {
			$db = new DatabaseHandler();
			$mail = $_GET["mail"];
			$resetQuestionQuery = $db->query(
				"SELECT question_number, answer_text, q.text_question
						FROM [User] AS u
						INNER JOIN Question AS q
						ON u.question = q.question_number
						WHERE mailbox = :mailbox",
				array(
					":mailbox" => $mail,
				)
			);

			if (count($resetQuestionQuery) > 0) {
				$this->data['eligibleForReset'] = true;
			} else {
				$this->redirect('/');
			}
		}
	}

	public function run() {
		$this->data["questionFromUser"] = $_GET["question"] ?? null;
		$this->data["mail"] = $_GET['mail'] ?? null;
		$this->data["resetError"] = $_GET["reset-error"] ?? null;
		$this->data["resetSuccess"] = $_GET["reset-success"] ?? null;
		$this->checkCredentials();
		$this->render();
	}
}
