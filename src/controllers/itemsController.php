<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/controllers/baseController.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/database/databaseHandler.php";

class ItemsController extends BaseController {
	private function getSafePageNumber() {
		$unsafePageNumber = intval($_GET["page"] ?? 1);

		if ($unsafePageNumber < 1) $unsafePageNumber = 1;

		return $unsafePageNumber;
	}

	private function getAvailablePageNumbers(int $currPage, int $maxPages) {
		$ret = array();
		$currPage = min($maxPages - 3, max(2, $currPage));

		for ($i = $currPage - 2; $i < $currPage + 3; $i++) {
			$ret[] = $i;
		}
		return $ret;
	}

	// public function calculateRadius() {
	// 	$dbh = new DatabaseHandler();

	// 	if (isset($_GET["distance"])) {
	// 		if (isset($_SESSION["username"])) {

	// 			$username = $_SESSION["username"];
	// 			$distance = $_GET["distance"];

	// 			bdump('afstand: ' . $distance);


	// 				bdump($result);



	// 				if ($result <= $distance) {
	// 					bdump($result);
	// 				} else {
	// 					//Show NULL
	// 				}
	// 			}
	// 		}
	// 	}
	// }

	/**
	 * @param $lat1
	 * @param $long1
	 * @param $lat2
	 * @param $long2
	 * @return array
	 */
	public function calculateDistance($lat1, $long1, $lat2, $long2) {
		$theta = $long1 - $long2;
		$miles = (sin(deg2rad($lat1))) * sin(deg2rad($lat2)) + (cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)));
		$miles = acos($miles);
		$miles = rad2deg($miles);
		$result['miles'] = $miles * 60 * 1.1515;
		$result['feet'] = $result['miles'] * 5280;
		$result['yards'] = $result['feet'] / 3;
		$result['kilometers'] = $result['miles'] * 1.609344;

		return $result;
	}

	public function run() {
		$dbh = new DatabaseHandler();

		$this->data["page"] = $this->getSafePageNumber() - 1;
		$this->data["perPage"] = intval((isset($_GET["count"]) && $_GET["count"]) ? $_GET["count"] : 30);
		$this->data["minPrice"] = (isset($_GET["minPrice"]) && $_GET["minPrice"]) ? $_GET["minPrice"] : 0;
		$this->data["maxPrice"] = (isset($_GET["maxPrice"]) && $_GET["maxPrice"]) ? $_GET["maxPrice"] : 999999999999;
		$this->data["minPrice1"] = (isset($_GET["minPrice1"]) && $_GET["minPrice1"]) ? $_GET["minPrice1"] : 0;
		$this->data["maxPrice1"] = (isset($_GET["maxPrice1"]) && $_GET["maxPrice1"]) ? $_GET["maxPrice1"] : 999999999999;
		$this->data["distance"] = (isset($_GET["distance"]) && $_GET["distance"]) ? $_GET["distance"] : 999999999999;

		$username = $_SESSION["username"];

		$getUserLocationQuery = $dbh->query("SELECT longitude, latitude FROM [User] WHERE username = :username", array(
			":username" => $username
		));

		$this->data["items"] = $dbh->query(
			<<<SQL
				SELECT item_number, title, [description], [filename], bid_amount, row_count, latitude, longitude
					FROM vw_ItemsList
					WHERE bid_amount BETWEEN :minprice AND :maxprice
						AND dbo.fnCalcDistanceKM(latitude, :latUser, longitude, :lonUser) < :distance
					ORDER BY item_number
					OFFSET :offset ROWS
					FETCH FIRST :per_page ROWS ONLY;
			SQL,
			array(
				":offset" => $this->data["page"] * $this->data["perPage"],
				":per_page" => $this->data["perPage"],
				":minprice" => $this->data["minPrice"],
				":maxprice" => $this->data["maxPrice"],
				":distance" => $this->data["distance"],
				":latUser" => $getUserLocationQuery[0]["latitude"],
				":lonUser" => $getUserLocationQuery[0]["longitude"]
			)
		);

		$this->data["itemCount"] = $dbh->query(
			<<<SQL
				SELECT COUNT(*) AS 'count'
					FROM vw_ItemsList
					WHERE bid_amount BETWEEN :minprice1 AND :maxprice1
						AND dbo.fnCalcDistanceKM(latitude, :latUser, longitude, :lonUser) < :distance
			SQL,
			array(
				":minprice1" => $this->data["minPrice1"],
				":maxprice1" => $this->data["maxPrice1"],
				":distance" => $this->data["distance"],
				":latUser" => $getUserLocationQuery[0]["latitude"],
				":lonUser" => $getUserLocationQuery[0]["longitude"]
			)
		);

		// $calculateItemQuery = $dbh->query("SELECT latitude, longitude FROM vw_ItemsList");

		// $calculateDistanceQuery = $dbh->query("SELECT dbo.fnCalcDistanceKM(:latItem, :latUser, :lonItem, :lonUser)", array(
		// 	":latItem" => $calculateItemQuery[0]["latitude"],
		// 	":latUser" => $getUserLocationQuery[0]["latitude"],
		// 	":lonItem" => $calculateItemQuery[0]["longitude"],
		// 	":lonUser" => $getUserLocationQuery[0]["longitude"]
		// ));

		// bdump($calculateDistanceQuery);

		bdump($this->data, 'data');

		// if (count($this->data["items"]) <= 0) {
		// 	$this->redirect("/items/?page=1");
		// }

		// foreach ($this->data["items"] as $itemNumber) {
		// 	$this->data["imageNumbers"] = [$itemNumber][0]["item_number"];
		// 	$this->data["images"] = $dbh->query(
		// 		<<<SQL
		// 		SELECT [filename]
		// 			FROM [File]
		// 			WHERE item = :item
		// 		SQL,
		// 		array(
		// 			":item" => [$itemNumber][0]["item_number"]
		// 		)
		// 	);
		// }

		bdump($this->data["itemCount"]);

		$this->data["totalRows"] = $this->data["itemCount"][0]["count"];
		$this->data["nextPageNumbers"] = $this->getAvailablePageNumbers(
			$this->data["page"],
			ceil($this->data["totalRows"] / $this->data["perPage"])
		);

		$this->render();
	}
}
