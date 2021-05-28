<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';

class RequestHandler
{
	public function __construct()
	{
		session_start();

		if (!isset($_SESSION["csrf-token"]))
		{
			$_SESSION["csrf-token"] = bin2hex(random_bytes(32));
		}

		if (!isset($_SESSION["loggedin"]))
		{
			$_SESSION["loggedin"] = false;
		}
	}

	public function renderPath()
	{
		$requestPath = explode('?', $_SERVER["REQUEST_URI"])[0];
		$isPost = $_SERVER["REQUEST_METHOD"] === "POST";
		$folder = $isPost ? "api" : "controllers";
		$type = $isPost ? "Handler" : "Controller";
		$fileName = "";

		if ($requestPath === '/')
		{
			$fileName = "home";
		}
		else
		{
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

		try
		{
			// if the controller doesn't exist, send a 404 page
			if (!file_exists($fullPath))
			{
				require_once $_SERVER["DOCUMENT_ROOT"]
					. "/src/controllers/notFoundController.php";

				$instance = new NotFoundController($requestPath, "/", "notFound");
				$instance->run();
			}
			else
			{
				require_once $fullPath;

				$instance = new $class($requestPath, $filePath, $fileName);
				$instance->run();
			}
		}
		catch (NotImplementedException $e)
		{
			// TODO: 5xx page
			die('<h1 style="color: red">Whoopsie</h1><code>'
				. ' In "' . $e->getFile() . ':' . $e->getLine() . '"<br>'
				. $e->getMessage()
				. '</code>');
		}
		catch (Latte\RuntimeException $e)
		{
			die('<h1 style="color: red">Whoopsie</h1><code>'
				. ' In "' . $e->getFile() . ':' . $e->getLine() . '"<br>'
				. $e->getMessage()
				. '</code>');
		}
	}
}
