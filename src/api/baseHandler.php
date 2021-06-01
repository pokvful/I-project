<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/settings.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/api/baseHandler.php';

class BaseHandler {
	public string $requestPath;
	public string $filePath;
	public string $fileName;
	public array $data;

	public function __construct(string $requestPath, string $filePath, string $fileName) {
		$this->requestPath = $requestPath;
		$this->filePath = $filePath;
		$this->fileName = $fileName;

		if ( !BaseHandler::isValidRequest() ) {
			header("Content-Type: application/json");
			http_response_code(403);
			die(json_encode(
				array(
					"error" => true,
					"code" => 403,
					"message" => "Forbidden",
				)
			));
		}
	}

	private static function isValidRequest(): bool {
		$headers = null;

		return !(
			!isset($_POST["csrf-token"])
				|| !$_POST["csrf-token"]
				|| $_POST["csrf-token"] !== $_SESSION["csrf-token"]
		) || (
			( $headers = getallheaders() )
				&& isset( $headers["X-cronjob"] )
				&& $headers
				&& $headers["X-cronjob"] === SETTINGS["cronjob"]
		);
	}

	/**
	 * Redirect to `$url`. If `$url` is not send, the function uses the
	 * `redirect_uri` parameter in the request body
	 *
	 * @param string|null $url
	 */
	protected function redirect(string $url = null) {
		if (!$url) {
			if (!isset($_POST["redirect_uri"]))
				die("no redirect uri given");

			$url = $_POST["redirect_uri"];
		}

		header("Location: $url");
		exit();
	}

	/**
	 * @throws NotImplementedException
	 */
	public function run() {
		throw new NotImplementedException(
			"Api \"/src/api{$this->filePath}{$this->fileName}\""
				. " hasn't been implemented"
		);
	}
}
