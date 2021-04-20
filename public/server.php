<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/settings.php';

if ( SETTINGS["debug"] === true ) {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
}

//require_once $_SERVER["DOCUMENT_ROOT"] . "/src/database/databaseHandler.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/controller.php";

$requestHandler = new RequestHandler();

$requestHandler->renderPath();
