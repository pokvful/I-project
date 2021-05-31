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

		if($maxPages > 5) {
			for ($i = $currPage - 2; $i < $currPage + 3; $i++) {
				$ret[] = $i;
			}
		} else {
			for($i = 0; $i < $maxPages; $i++) {
				$ret[] = $i;
			}
		}
		return $ret;
	}

	public function calculateRadius() {
		$dbh = new DatabaseHandler();

		if (isset($_GET["distance"])) {
			if (isset($_SESSION["username"])) {

				$username = $_SESSION["username"];
				$distance = $_GET["distance"];

				bdump('afstand: ' . $distance);
				$getUserLocationQuery = $dbh->query("SELECT longitude, latitude FROM [User] WHERE username = :username", array(
					":username" => $username
				));

				$getItemLocationQuery = $dbh->query("SELECT TOP 30 longitude, latitude FROM Item");

				foreach ($getItemLocationQuery as $itemLocation) {
					$result = $this->calculateDistance(
						$getUserLocationQuery[0]["latitude"],
						$getUserLocationQuery[0]["longitude"],
						$itemLocation["latitude"],
						$itemLocation["longitude"]
					);

					if ($result <= $distance) {
						bdump($result);
					} else {
						//Show NULL
					}
				}
			}
		}
	}

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

		// $this->calculateRadius();

		if (isset($_GET["distance"]) && ($_GET["distance"] > 0)) {
			if (isset($_SESSION["username"])) {
				$username = $_SESSION["username"];
				$distance = $_GET["distance"];

				$getUserLocationQuery = $dbh->query("SELECT longitude, latitude FROM [User] WHERE username = :username", array(
					":username" => $username
				));

				$getItemLocationQuery = $dbh->query("SELECT TOP 30 longitude, latitude FROM Item");

				foreach ($getItemLocationQuery as $itemLocation) {
					$result = $this->calculateDistance(
						$getUserLocationQuery[0]["latitude"],
						$getUserLocationQuery[0]["longitude"],
						$itemLocation["latitude"],
						$itemLocation["longitude"]
					);
					if ($distance <= $result) {
						bdump($result);
					}
				}
			}
		}

		
		$this->data["page"] = $this->getSafePageNumber() - 1;
		$this->data["perPage"] = intval($_GET["count"] ?? 30);
		$this->data["minPrice"] = $_GET["minPrice"] ?? 0;
		$this->data["maxPrice"] = $_GET["maxPrice"] ?? PHP_INT_MAX;
		
		//Builds URL for signup-errors
		$addressRoot = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER["SERVER_NAME"] . "/items/";
		$redirectAddress = $addressRoot;

		if ($this->data["minPrice"] > $this->data["maxPrice"]) {
			$this->redirect(
				$redirectAddress . "?signup-error=" . urlencode("Prijs filter ongeldig.")
			);
		} else if (($this->data["minPrice"] || $this->data["maxPrice"]) < 1) {
			$this->redirect(
				$redirectAddress . "?signup-error=" . urlencode("Prijs filter mag geen negatieve waarden bevatten.")
			);
		}	

		$this->data["signupError"] = $_GET["signup-error"] ?? null;

		$this->data["items"] = $dbh->query(
			<<<SQL
				SELECT item_number, title, [description], [filename], bid_amount, row_count
					FROM vw_ItemsList
					WHERE bid_amount BETWEEN :minprice AND :maxprice
					ORDER BY item_number
					OFFSET :offset ROWS
					FETCH FIRST :per_page ROWS ONLY;
			SQL,
			array(
				":offset" => $this->data["page"] * $this->data["perPage"],
				":per_page" => $this->data["perPage"],
				":minprice" => $this->data["minPrice"],
				":maxprice" => $this->data["maxPrice"],
			)
		);

		$this->data["itemCount"] = $dbh->query(
			<<<SQL
				SELECT COUNT(*) AS 'count'
					FROM vw_ItemsList
					WHERE bid_amount BETWEEN :minprice AND :maxprice;
			SQL,
			array(
				":minprice" => $this->data["minPrice"],
				":maxprice" => $this->data["maxPrice"],
			)
		);

		if (count($this->data["items"]) <= 0) {
			$this->redirect("/items/?page=1");
		}

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

		$this->data["totalRows"] = $this->data["itemCount"][0]["count"];
		$this->data["nextPageNumbers"] = $this->getAvailablePageNumbers(
			$this->data["page"],
			ceil($this->data["totalRows"] / $this->data["perPage"])
		);

		$this->render();
	}
}
