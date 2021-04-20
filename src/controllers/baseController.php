<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/settings.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';

class NotImplementedException extends Exception {
	public function __construct(string $message, int $code = 0, Throwable $previous = null) {
		parent::__construct($message, $code, $previous);
	}
}

class BaseController {
	private Latte\Engine $latteEngine;

	public string $requestPath;
	public string $filePath;
	public string $fileName;
	public array $data;

	public function __construct(string $requestPath, string $filePath, string $fileName) {
		$this->latteEngine = new Latte\Engine;

		$this->latteEngine->setTempDirectory(SETTINGS["latte"]["tempDirectory"]);

		$this->requestPath = $requestPath;
		$this->filePath = $filePath;
		$this->fileName = $fileName;
		$this->data = array(
			"server" => array(
				"requestPath" => $requestPath,
				"filePath" => $filePath,
				"fileName" => $fileName,
			),
		);
	}

	/**
	 * Redirect the user to a url
	 *
	 * @param string $path The url the user should be send to
	 */
	protected function redirect(string $path) {
		header("Location: " . $path);
	}

	/**
	 * Render the template
	 */
	protected function render() {
		$this->latteEngine->render(
			$_SERVER["DOCUMENT_ROOT"] . "/src/views/"
			. $this->filePath . "{$this->fileName}.latte",
			$this->data,
		);
	}

	public function run() {
		throw new NotImplementedException(
			"Controller \"/src/controllers{$this->filePath}{$this->fileName}\""
			. " hasn't been implemented"
		);
	}
}

