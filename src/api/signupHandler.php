<?php

class signupHandler extends baseHandler {
	public function run() {

		$email = new Email("Signup email");
		$email->addAddress("jorambuitenhuis@gmail.com");
		$email->setText("Hey, <b>dit</b> is een test");
		$email->send();
		echo "Done :)";

		echo "test signupHandler";

		if (!isset($_POST['email'])){
			$this->redirect("/signup");
		}
			$email = $_POST['email']; //is ingevulde email



		}
		$email = "jorey.lensink@gmail.com";
		$verivicationLink = password_hash(time() . $_POST['email']);

		var_dump($verivicationLink);
		die();
//$hash = "fddfd";
//		$_SESSION["signup-hash"] = $hash;
//		$url = $_SERVER[""] . "?emailhash=" . hash;

		$email = new Email("Nieuwe test mail!");
		$email->addAddress("jorambuitenhuis@gmail.com");
		$email->setText("Hey, <b>dit</b> is een test");
		$email->send();
		echo "Done :)";
		//eventeel redirect

}
