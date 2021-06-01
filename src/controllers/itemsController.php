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

		if ($maxPages > 5) {
			for ($i = $currPage - 2; $i < $currPage + 3; $i++) {
				$ret[] = $i;
			}
		} else {
			for ($i = 0; $i < $maxPages; $i++) {
				$ret[] = $i;
			}
		}
		return $ret;
	}

	public function run() {
		$dbh = new DatabaseHandler();

		$this->data["page"] = $this->getSafePageNumber() - 1;
		$this->data["perPage"] = intval($_GET["count"] ?? 30);
		$this->data["maxBid"] = $dbh->query("SELECT MAX(bid_amount) as maxBid FROM vw_ItemsList");

		$maxPrice = $_GET["maxPrice"] ?? $this->data["maxBid"][0]["maxBid"];
		$minPrice = $_GET["minPrice"] ?? 1;

		if (!$maxPrice) {
			$maxPrice = $this->data["maxBid"][0]["maxBid"];
		}

		$this->data["perPage"] = intval((isset($_GET["count"]) && $_GET["count"]) ? $_GET["count"] : 30);
		$this->data["minPrice"] = (isset($_GET["minPrice"]) && $_GET["minPrice"]) ? $_GET["minPrice"] : 0;
		$this->data["maxPrice"] = (isset($_GET["maxPrice"]) && $_GET["maxPrice"]) ? $_GET["maxPrice"] : 999999999;
		$this->data["minPrice1"] = (isset($_GET["minPrice1"]) && $_GET["minPrice1"]) ? $_GET["minPrice1"] : 0;
		$this->data["maxPrice1"] = (isset($_GET["maxPrice1"]) && $_GET["maxPrice1"]) ? $_GET["maxPrice1"] : 999999999;
		$this->data["distance"] = (isset($_GET["distance"]) && $_GET["distance"]) ? $_GET["distance"] : 999999999;
		$this->data["signupError"] = $_GET["signup-error"] ?? null;

		//Builds URL for signup-errors
		$addressRoot = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER["SERVER_NAME"] . "/items/";

		if ($minPrice >= $maxPrice) {
			$this->redirect(
				$addressRoot . "?signup-error=" . urlencode("De minimum prijs mag niet hoger zijn dan de maximum prijs.")
			);
		}

		$this->data["signupError"] = $_GET["signup-error"] ?? null;

		if ($this->data["minPrice"] > $this->data["maxPrice"]) {
			$this->redirect(
				$addressRoot . "?signup-error=" . urlencode("Prijs filter ongeldig.")
			);
		} else if (($this->data["minPrice"] || $this->data["maxPrice"]) < 1) {
			$this->redirect(
				$addressRoot . "?signup-error=" . urlencode("Prijs filter mag geen negatieve waarden bevatten.")
			);
		}

		if (isset($_SESSION["username"])) {
			$username = $_SESSION["username"];
		} else {
			$this->redirect(
				"/"
			);
		}

		$getUserLocationQuery = $dbh->query("SELECT latitude, longitude FROM [User] WHERE username = :username", array(
			":username" => $username
		));

		$this->data["items"] = $dbh->query(
			<<<SQL
				SELECT item_number, title, [description], [filename], bid_amount, latitude, longitude, row_count
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
				":minprice" => $minPrice,
				":maxprice" => $maxPrice,
				":distance" => $this->data["distance"],
				":latUser" => $getUserLocationQuery[0]["latitude"],
				":lonUser" => $getUserLocationQuery[0]["longitude"]
			)
		);

		$this->data["itemCount"] = $dbh->query(
			<<<SQL
				SELECT COUNT(*) AS 'count'
					FROM vw_ItemsList
					WHERE bid_amount BETWEEN :minprice AND :maxprice
						AND dbo.fnCalcDistanceKM(latitude, :latUser, longitude, :lonUser) < :distance
			SQL,
			array(
				":minprice" => $minPrice,
				":maxprice" => $maxPrice,
				":distance" => $this->data["distance"],
				":latUser" => $getUserLocationQuery[0]["latitude"],
				":lonUser" => $getUserLocationQuery[0]["longitude"]
			)
		);

		bdump($this->data, 'data');

		bdump($this->data["itemCount"]);

		$this->data["totalRows"] = $this->data["itemCount"][0]["count"];
		$this->data["nextPageNumbers"] = $this->getAvailablePageNumbers(
			$this->data["page"],
			ceil($this->data["totalRows"] / $this->data["perPage"])
		);
		$this->data["minPrice"] = $minPrice;
		$this->data["maxPrice"] = $maxPrice;

		$calculateItemQuery = $dbh->query("SELECT latitude, longitude FROM vw_ItemsList");

		$calculateDistanceQuery = $dbh->query("SELECT dbo.fnCalcDistanceKM(:latItem, :latUser, :lonItem, :lonUser)", array(
			":latItem" => $calculateItemQuery[0]["latitude"],
			":latUser" => $getUserLocationQuery[0]["latitude"],
			":lonItem" => $calculateItemQuery[0]["longitude"],
			":lonUser" => $getUserLocationQuery[0]["longitude"]
		));

		bdump($calculateDistanceQuery);

		$this->render();
	}
}
