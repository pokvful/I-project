<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/database/databaseHandler.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/api/baseHandler.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/email.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/api/signupHandler.php';

class SellerSignupHandler extends BaseHandler {

	public function run() {
		$dbh = new DatabaseHandler();
		$this->data["isSeller"] = $dbh->query(
			"SELECT seller FROM [User] WHERE [username] = :user",
			array(
				":user" => $_SESSION["username"]
			)
		);
		if ($this->data["isSeller"][0]["seller"] == 1) {
			$this->redirect("/");
		} else {
			$bank = $_POST["bank_name"];
			$bankAccount = $_POST["bank_account"];
			$paymentMethod = $_POST['payment_method'] ?? null;
			//Builds URL for signup-errors
			$addressRoot = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER["SERVER_NAME"] . "/sellerSignup/";
			if (isset($_POST["creditcard_number"])) {
				$creditcard = $_POST["creditcard_number"];
				if (!ctype_digit($creditcard)) {
					$this->redirect($addressRoot . "?signup-error=" . urlencode("Ongeldige creditcardnummer."));
				}
				$creditcardIsSet = true;
			} else {
				$creditcardIsSet = null;
			}
			//Filters form inputs
			if (!isset($bank) || !$bank) {
				$this->redirect(
					$addressRoot . "?signup-error=" . urlencode("Banknaam is niet ingevuld.")
				);
			}
			if (!isset($bankAccount) || !$bankAccount) {
				$this->redirect(
					$addressRoot . "?signup-error=" . urlencode("Rekeningnummer is niet ingevuld.")
				);
			}
			if ($paymentMethod == 'creditcard' && !$creditcard) {
				$this->redirect(
					$addressRoot . "?signup-error=" . urlencode("Creditcardnummer is niet ingevuld.")
				);
			}
			if ($paymentMethod == 'post' && $creditcard != '') {
				$this->redirect(
					$addressRoot . "?signup-error=" . urlencode("U kan geen creditcard ingeven als uw betaalmethode op post staat.")
				);
			}
			if (!isset($paymentMethod) || !$paymentMethod) {
				$this->redirect(
					$addressRoot . "?signup-error=" . urlencode("Betaalmethode is niet ingevuld.")
				);
			}
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

			if (!$creditcardIsSet) {
				$dbh->query(
					<<<SQL
						INSERT INTO Seller ([user], bank, bank_account, control_option)
										VALUES (:username, :bank, :bank_account, :payment_method)
					SQL,
					array(
						":username" => $_SESSION["username"],
						":bank" => $bank,
						":bank_account" => $bankAccount,
						":payment_method" => $paymentMethod,
					)
				);
			} else {
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
			} 

			$this->redirect("/profile/");
		}
	}
}
