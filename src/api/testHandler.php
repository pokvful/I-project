<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/email.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/api/baseHandler.php';

class TestHandler extends baseHandler {
	public function run() {
		$email = new Email("De alleer eerste test mail :)");
		$email->addAddress("jorambuitenhuis@gmail.com");
		$email->setText("Hey, <b>dit</b> is een test");
		$email->send();
		echo "Done :)";
	}
}
