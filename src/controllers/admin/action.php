<?php
	require '../../controllers/admin/testGoogleMapsAPIController.php';

    $APIController = new TestGoogleMapsAPIController;
	$APIController->setLat($_REQUEST["lat"]);
	$APIController->setLng($_REQUEST["long"]);
	$APIController->updateLatitudeAndLongitude();