<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';

class RequestHandler {
	public function __construct() {
		session_start();

		if (!isset($_SESSION["csrf-token"])) {
			$_SESSION["csrf-token"] = bin2hex(random_bytes(32));
		}

		if (!isset($_SESSION["loggedin"])) {
			$_SESSION["loggedin"] = false;
		} else if ($_SESSION["loggedin"]) {
			$db = new DatabaseHandler();

			$users = $db->query(
				"SELECT [admin], seller FROM [User] WHERE username = :username",
				array(
					":username" => $_SESSION["username"]
				)
			);

			if (count($users) > 0) {
				$_SESSION["seller"] = $users[0]["seller"] == 1;
				$_SESSION["admin"] = $users[0]["admin"] == 1;
			} else {
				$_SESSION["seller"] = $_SESSION["admin"] = false;
			}
		} else {
			$_SESSION["seller"] = $_SESSION["admin"] = false;
		}
	}

	public function renderPath() {
		$requestPath = explode('?', $_SERVER["REQUEST_URI"])[0];
		$isPost = $_SERVER["REQUEST_METHOD"] === "POST";
		$folder = $isPost ? "api" : "controllers";
		$type = $isPost ? "Handler" : "Controller";
		$fileName = "";

		if ($requestPath === '/') {
			$fileName = "home";
		} else {
			// get the last folder from the uri, and place it in `$matches[1]`
			// if the uri is `/admin/users/`, `$matches[1]` should be `users`
			$matches = array();
			preg_match('/([^\/]*)\/?$/', $requestPath, $matches);

			$fileName = $matches[1];
		}

		$class = ucfirst($fileName) . $type;
		// remove the last folder from the uri to get the folder the template
		// and controller are in
		$filePath = preg_replace('/([^\/]*)\/?$/', '', $requestPath);
		$fullPath = $_SERVER["DOCUMENT_ROOT"] . "/src/$folder/"
			. "{$filePath}{$fileName}{$type}.php";

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
			die('<h1 style="color: red">Whoopsie</h1><code>'
				. ' In "' . $e->getFile() . ':' . $e->getLine() . '"<br>'
				. $e->getMessage()
				. '</code>');
		} catch (Latte\RuntimeException $e) {
			die('<h1 style="color: red">Whoopsie</h1><code>'
				. ' In "' . $e->getFile() . ':' . $e->getLine() . '"<br>'
				. $e->getMessage()
				. '</code>');
		}
	}
}
