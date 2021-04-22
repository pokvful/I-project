<?php

class RequestHandler {
	public function renderPath() {
		$requestPath = $_SERVER["REQUEST_URI"];

		$fileName = "";

		if ($requestPath === '/') {
			$fileName = "home";
		} else {
			// get the last folder from the uri, and place it in `$matches[1]`
			// if the uri is `/admin/users/`, `$matches[1]` should be `users`
			$matches = array();
			preg_match('/([^\/]*)\/?$/', $requestPath, $matches);

			$match = $matches[1];
			$fileName = $match;
		}

		$class = ucfirst($fileName) . "Controller";
		// remove the last folder from the uri to get the folder the template
		// and controller are in
		$filePath = preg_replace('/([^\/]*)\/?$/', '', $requestPath);
		$fullPath = $_SERVER["DOCUMENT_ROOT"] . "/src/controllers/"
			. $filePath . "{$fileName}Controller.php";

		try {
			// if the controller doesn't exist, send a 404 page
			if (!file_exists($fullPath)) {
				require_once $_SERVER["DOCUMENT_ROOT"]
					. "/src/controllers/notFoundController.php";

				$instance = new NotFoundController($requestPath, "/", "notFound");
				$instance->run();
			} else {
				require_once $fullPath;

				$instance = new $class($requestPath, $filePath, $fileName);
				$instance->run();
			}
		} catch (NotImplementedException $e) {
			// TODO: 5xx page
			die(
				'<h1 style="color: red">Whoopsie</h1><code>'
				. ' In "' . $e->getFile() . ':' . $e->getLine() . '"<br>'
				. $e->getMessage()
				. '</code>'
			);
		}
	}
}

