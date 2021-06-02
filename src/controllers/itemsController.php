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
		
		$rubrics = RubricHelper::getRubricsFromDataBase();
		$this->data["rubrics"] = RubricHelper::getEndRubrics($rubrics);

		$this->data["page"] = $this->getSafePageNumber() - 1;
		$this->data["perPage"] = intval((isset($_GET["count"]) && $_GET["count"]) ? $_GET["count"] : 30);
		$this->data["minPrice"] = (isset($_GET["minPrice"]) && $_GET["minPrice"]) ? $_GET["minPrice"] : 0;
		$this->data["maxPrice"] = (isset($_GET["maxPrice"]) && $_GET["maxPrice"]) ? $_GET["maxPrice"] : 999999999;
		$this->data["distance"] = (isset($_GET["distance"]) && $_GET["distance"]) ? $_GET["distance"] : 999999999;
		$this->data["rubric_wanted"] = (isset($_GET["rubric"]) && $_GET["rubric"]) ? $_GET["rubric"] : -1;
		$this->data["error"] = $_GET["error"] ?? null;

		$addressRoot = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER["SERVER_NAME"] . "/items/";

		$this->data["error"] = $_GET["error"] ?? null;

		if ($this->data["minPrice"] > $this->data["maxPrice"]) {
			$this->redirect(
				$addressRoot . "?error=" . urlencode("De minimum prijs mag niet hoger zijn dan de maximum prijs.")
			);
		} else if (($this->data["minPrice"] || $this->data["maxPrice"]) < 1) {
			$this->redirect(
				$addressRoot . "?error=" . urlencode("Prijs filter mag geen negatieve waarden bevatten.")
			);
		} else if ( !(
			$this->data["rubric_wanted"] == -1
				|| count(
					array_filter( $this->data["rubrics"], function(Rubric $v) {
						return $v->id == $this->data["rubric_wanted"];
					} )
				) > 0
			)) {
			$this->redirect(
				$addressRoot . "?error=" . urldecode("Ongeldige categorie opgegeven.")
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
						AND (:rubric = -1 OR rubric_number = :rubric_number)
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
				":lonUser" => $getUserLocationQuery[0]["longitude"],
				":rubric" => $this->data["rubric_wanted"],
				":rubric_number" => $this->data["rubric_wanted"],
			)
		);

		$this->data["itemCount"] = $dbh->query(
			<<<SQL
				SELECT COUNT(*) AS 'count'
					FROM vw_ItemsList
					WHERE bid_amount BETWEEN :minprice AND :maxprice
						AND dbo.fnCalcDistanceKM(latitude, :latUser, longitude, :lonUser) < :distance
						AND (:rubric = -1 OR rubric_number = :rubric_number)
			SQL,
			array(
				":minprice" => $this->data["minPrice"],
				":maxprice" => $this->data["maxPrice"],
				":distance" => $this->data["distance"],
				":latUser" => $getUserLocationQuery[0]["latitude"],
				":lonUser" => $getUserLocationQuery[0]["longitude"],
				":rubric" => $this->data["rubric_wanted"],
				":rubric_number" => $this->data["rubric_wanted"],
			)
		);

		$this->data["totalRows"] = $this->data["itemCount"][0]["count"];
		$this->data["nextPageNumbers"] = $this->getAvailablePageNumbers(
			$this->data["page"],
			ceil($this->data["totalRows"] / $this->data["perPage"])
		);

		$this->render();
	}
}
