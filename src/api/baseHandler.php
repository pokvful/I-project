<?php

class BaseHandler {
	public string $requestPath;
	public string $filePath;
	public string $fileName;
	public array $data;

	public function __construct(string $requestPath, string $filePath, string $fileName) {
		$this->requestPath = $requestPath;
		$this->filePath = $filePath;
		$this->fileName = $fileName;

		if (
			!isset( $_POST["csrf-token"])
			|| !$_POST["csrf-token"]
			|| $_POST["csrf-token"] !== $_SESSION["csrf-token"]
		) {
			header("Content-Type: application/json");
			http_response_code(403);
			die(
				json_encode(
					array(
						"error" => true,
						"code" => 403,
						"message" => "Forbidden",
					)
				)
			);
		}
	}

	/**
	 * Redirect to `$url`. If `$url` is not send, the function uses the
	 * `redirect_uri` parameter in the request body
	 * 
	 * @param string [$url=null] - The redirect url
	 */
	private function redirect(string $url = null) {
		if (!$url) {
			if ( !isset( $_POST["redirect_uri"] ) )
				die("no redirect uri given");

			$url = $_POST["redirect_uri"];
		}

		header("Location $url");
	}

	public function run() {
		throw new NotImplementedException(
			"Api \"/src/api{$this->filePath}{$this->fileName}\""
			. " hasn't been implemented"
		);
	}
}