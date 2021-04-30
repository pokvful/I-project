<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/email.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/api/baseHandler.php';

class TestHandler extends baseHandler {
	public function run() {
		$email = "joreylensink@gmail.com";
		$verivicationLink = password_hash(time() . $email, PASSWORD_DEFAULT);

		var_dump($verivicationLink);
		die();

//		$hash = "fddfd";
//		$_SESSION["signup-hash"] = $hash;
//		$url = $_SERVER[""] . "?emailhash=" . hash;

		$email = new Email("Nieuwe test mail!");
		$email->addAddress("jorambuitenhuis@gmail.com");
		$email->setText("Hey, <b>dit</b> is een test");
		$email->send();
		echo "Done :)";
		//eventeel redirect
	}
}
