<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/settings.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';

class NotImplementedException extends Exception { }

class BaseController {
	private Latte\Engine $latteEngine;

	public string $requestPath;
	public string $filePath;
	public string $fileName;
	public array $data;

	public function __construct(string $requestPath, string $filePath, string $fileName) {
		$this->latteEngine = new Latte\Engine;

		$this->latteEngine->setTempDirectory(SETTINGS["latte"]["tempDirectory"]);

		$this->latteEngine->addFunction('getCsrfInput', function(): string {
			return "<input type=\"hidden\" name=\"csrf-token\" value=\"{$_SESSION["csrf-token"]}\" />";
		});

		$this->latteEngine->addFunction('getBreadCrumbs', function (): string {
			$newPaths = array_filter(explode('/', $this->requestPath));
			$currentPath = "/";
			$i = 0;
			$result = "<ul class=\"breadcrumb\">";

			foreach ($newPaths as $path) {
				$currentPath .= "{$path}/";
				if ($i === count($newPaths) - 1) {
					$result .= "<li class=\"breadcrumb-item active\"> {$path} </li>";
				} else {
					$result .= "<li class=\"breadcrumb-item\"> <a href=\"{$currentPath}\"> {$path} </a></li>";
				}
				$i++;
			}

			$result .= "</ul>";

			return $result;
		});

		$this->requestPath = $requestPath;
		$this->filePath = $filePath;
		$this->fileName = $fileName;
		$this->data = array(
			"_requestPath" => $requestPath,
			"_filePath" => $filePath,
			"_fileName" => $fileName,
			"_params" => $_GET,
			"_csrfToken" => $_SESSION["csrf-token"],
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

	/**
	 * @throws NotImplementedException
	 */
	public function run() {
		throw new NotImplementedException(
			"Controller \"/src/controllers{$this->filePath}{$this->fileName}\""
			. " hasn't been implemented"
		);
	}
}
