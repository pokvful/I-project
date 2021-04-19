<?php
class Controller {
	public function render() {
		$requestPath = $_SERVER["REQUEST_URI"];

		$fileName = "";

		if ($requestPath === '/') {
			$fileName = "Home";

			// TODO: Dit ziet er niet uit
			require_once $_SERVER["DOCUMENT_ROOT"] . "/src/views/homeView.php";
		} else {
			$matches = array();
			preg_match('/([^\/]*)\/?$/', $requestPath, $matches);

			$match = $matches[1];
			$fileName = ucfirst($match);
		}

		$class = $fileName . "View";
		$path = preg_replace('/([^\/]*)\/?$/', '', $requestPath);

		require_once $_SERVER["DOCUMENT_ROOT"] . "/src/views/"
			. $path . lcfirst($class) . ".php";

		$instance = new $class( $requestPath, $path, lcfirst($fileName) );
		$instance->run();
	}
}

