<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/settings.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';

use Tracy\Debugger;

if (SETTINGS["debug"] === true) {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	Debugger::$strictMode = true;
	Debugger::$dumpTheme = 'dark';
	Debugger::enable();
}

require_once $_SERVER["DOCUMENT_ROOT"] . "/src/database/databaseHandler.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/requestHandler.php";

$requestHandler = new RequestHandler();

$requestHandler->renderPath();
