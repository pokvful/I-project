<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/controllers/baseController.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/database/databaseHandler.php';

class ResetController extends BaseController
{

	public function checkCredentials()
	{
		$this->data['eligibleForReset'] = false;

		if (!isset($_GET["hash"]) && !isset($_GET["mail"])) {

		} else {
			$mail = $_GET["mail"];

			$db = new DatabaseHandler();


			$resetQuestionQuery = $db->query(
				"SELECT question_number, answer_text
						FROM [User] AS u
						INNER JOIN Question AS q
						ON u.question = q.question_number
						WHERE mailbox = :mailbox",
				array(
					":mailbox" => $mail,
				));

			$question = $resetQuestionQuery[0]['question'];
			$questionAnswer = $resetQuestionQuery[0]['answer_text'];

			$this->data['eligibleForReset'] = true;
		}
	}

	public function run()
	{
		$this->data["resetError"] = $_GET["reset-error"] ?? null;
		$this->data["resetSuccess"] = $_GET["reset-success"] ?? null;
		$this->checkCredentials();
		$this->render();
	}
}
