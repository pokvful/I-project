<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/database/databaseHandler.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/api/baseHandler.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/email.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/api/signupHandler.php';

class SellerSignupHandler extends BaseHandler {

	public function run() {
		$this->data["isSeller"] = $dbh->query("SELECT Seller FROM [User] WHERE [username] = :user",
			array(
				":user" => $_SESSION["username"]
			)
		);
		if ($this->data["isSeller"][0]["Seller"] == 1) {
			$this->redirect("/");
		} else {
			$this->render();

			$dbh = new DatabaseHandler();


			$bank = $_POST["bank_name"];
			$bankAccount = $_POST["bank_account"];
			$paymentMethod = $_POST['payment_method'];
			$creditcard = $_POST["creditcard_number"];
			//Builds URL for signup-errors
			$addressRoot = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER["SERVER_NAME"] . "/sellerSignup/";
			$redirectAddress = $addressRoot;

			//Filters form inputs
			if (!isset($bank) || !$bank) {
				$this->redirect($redirectAddress . "?signup-error=" . urlencode("Banknaam is niet ingevuld.")
				);
			}
			if (!isset($bankAccount) || !$bankAccount) {
				$this->redirect($redirectAddress . "?signup-error=" . urlencode("Rekeningnummer is niet ingevuld")
				);
			}
			if (!isset($creditcard) || !$creditcard) {
				$this->redirect($redirectAddress . "?signup-error=" . urlencode("Creditcardnummer is niet ingevuld.")
				);
			}
			if ($paymentMethod == 'post' && $creditcard != '') {
				$this->redirect($redirectAddress . "?signup-error=" . urlencode("U kan geen creditcard ingeven als uw betaalmethode op post staat.")
				);
			}

			$dbh->query(
				<<<SQL
					INSERT INTO Seller ([user], bank, bank_account, control_option, creditcard)
									VALUES (:username, :bank, :bank_account, :payment_method, :creditcard_number)
				SQL,
				array(
					":username" => $_SESSION["username"],
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
					":username" => $_SESSION["username"],
				)
			);

			$this->redirect("/profile/");

		}
	}
}