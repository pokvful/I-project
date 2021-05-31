<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/helpers/rubric.php';
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/controllers/baseController.php";

class RubricsController extends BaseController {
	public function run() {
		$rubrics = RubricHelper::getRubricsFromDataBase();

		$rubricTree = array_filter(
			explode('\\', urldecode($_GET["rubric"] ?? "")),
			function ($v) {
				return !!$v;
			}
		);

		$i = 0;
		$currentPath = array();
		$breadcrumbs = <<<HTML
			<ul class="breadcrumb">
				<li class="breadcrumb-item">
					<a href="/rubrics/">Categorie&euml;n</a>
				</li>
		HTML;

		foreach ($rubricTree as $rubric) {
			$currentPath[] = $rubric;
			$text = htmlentities(urldecode($rubric), ENT_QUOTES);

			if ($i === count($rubricTree) - 1) {
				$breadcrumbs .= "<li class=\"breadcrumb-item active\"> {$text} </li>";
			} else {
				$breadcrumbs .= "<li class=\"breadcrumb-item\"><a href=\"/rubrics/?rubric="
					. urlencode(implode('\\', $currentPath)) . "\"> {$text} </a></li>";
			}

			$i++;
		}

		$breadcrumbs .= "</ul>";

		$rubricWanted = $rubricTree;

		while (count($rubricWanted) > 0 && !is_null($rubrics)) {
			$rubric = array_shift($rubricWanted);

			$rubrics = $rubrics->getByName($rubric);
		}

		$this->data["rubricTree"] = urlencode(implode('\\', $rubricTree));
		$this->data["rubrics"] = $rubrics;
		$this->data["breadcrumbs"] = $breadcrumbs;

		$this->render();
	}
}
