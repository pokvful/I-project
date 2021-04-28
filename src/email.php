<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/settings.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Email {
	private PHPMailer $mailer;

	public function __construct( string $subject, array $from = SETTINGS["email"]["from"] ) {
		$this->mailer = new PHPMailer( SETTINGS["debug"] );

		$this->mailer->SMTPDebug = SETTINGS["debug"]
			? SMTP::DEBUG_CONNECTION
			: SMTP::DEBUG_OFF;
		$this->mailer->isSMTP();
		$this->mailer->Host = SETTINGS["email"]["host"];
		$this->mailer->SMTPAuth = false;
		$this->mailer->Port = SETTINGS["email"]["port"];

		$this->mailer->setFrom( $from["email"], $from["name"] );
		$this->mailer->isHTML(true);
		$this->mailer->Subject = $subject;
	}

	public function addAddress($emailAddresses, string $name = null) {
		if ( !$name )
			$emailAddresses = array( "$name" => "$emailAddresses" );

		foreach($emailAddresses as $name => $email) {
			if ( gettype($name) === 'string' ) {
				$this->mailer->addAddress($email, $name);
			} else {
				$this->mailer->addAddress($email);
			}
		}
	}

	public function setText(string $body) {
		// TODO: Maybe add a email template around `$body`
		$this->mailer->Body = $body;
		// TODO: Maybe some tags should stay, like links
		// we could also do some regex parsing to go from `<a href="link">text</a>`
		// to something like `text (link)`
		$this->mailer->AltBody = strip_tags($body);
	}

	public function send() {
		try {
			$this->mailer->send();
		} catch (Exception $e) {
			die( $e->getMessage() );
		}
	}
}
