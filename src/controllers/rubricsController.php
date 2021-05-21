<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/helpers/rubric.php';
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/controllers/baseController.php";

class RubricsController extends BaseController {
	public function run() {
		$rubrics = RubricHelper::getRubricsFromDataBase();

		$rubricTree = explode('\\', urldecode($_GET["rubric"] ?? ""));
		$rubricWanted = array_filter($rubricTree, function ($v) {
			return !!$v;
		});

		while (count($rubricWanted) > 0 && !is_null($rubrics)) {
			$rubric = array_shift($rubricWanted);

			$rubrics = $rubrics->getByName($rubric);
		}

		bdump($rubrics);

		$this->data["rubricTree"] = implode('\\', $rubricTree);
		$this->data["rubrics"] = $rubrics;

		$this->render();
	}
}
