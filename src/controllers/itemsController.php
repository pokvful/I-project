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

	public function run() {
		$dbh = new DatabaseHandler();

		$this->data["page"] = $this->getSafePageNumber() - 1;
		$this->data["perPage"] = intval($_GET["count"] ?? 30);

		$this->data["items"] = $dbh->query(
			<<<SQL
				SELECT item_number, title, [description], [filename], bid_amount, row_count
					FROM vw_ItemsList
					ORDER BY item_number
					OFFSET :offset ROWS
					FETCH FIRST :per_page ROWS ONLY
			SQL,
			array(
				":offset" => $this->data["page"] * $this->data["perPage"],
				":per_page" => $this->data["perPage"],
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

		bdump( $this->data["items"], 'items' );

		$this->data["totalRows"] = $this->data["items"][0]["row_count"];
		$this->data["nextPageNumbers"] = $this->getAvailablePageNumbers(
			$this->data["page"],
			ceil($this->data["totalRows"] / $this->data["perPage"])
		);

		$this->render();
	}
}
