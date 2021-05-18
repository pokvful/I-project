<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/database/databaseHandler.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/api/baseHandler.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/email.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/api/signupHandler.php';

class SellerSignupHandler extends BaseHandler {

	public function run() {
		$dbh = new DatabaseHandler();

		$bank = $_POST["bank_name"];
		$bankAccount = $_POST["bank_account"];
		$paymentMethod = $_POST["payment_method"];
		$creditcard = $_POST["creditcard_number"];

		dump($bank,$bankAccount,$paymentMethod,$creditcard);

		if (isset($_POST["seller"])){
			$this->redirect("/");
		}
	}
}
