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
		$paymentMethod = $_POST['payment_method'];
		$creditcard = $_POST["creditcard_number"];

		


		$dbh->query(
			<<<SQL
				INSERT INTO Seller ([user], bank, bank_account, control_option, creditcard)
								VALUES (:username, :bank, :bank_account, :payment_method, :creditcard_number)
	
			SQL,
			array(
				":username"	=> $_SESSION["username"],
				":bank" => $bank,
				":bank_account" => $bankAccount,
				":payment_method" => $paymentMethod,
				":creditcard_number" => $creditcard,
			)
		);	
		$dbh->query(
			<<<SQL
			UPDATE 	[User]
			SET seller = 1
			WHERE [username] = :username							
			SQL,
			array(
				":username"	=> $_SESSION["username"],
			)
		);	
			
			$this->redirect("/profile/");

	}
}
