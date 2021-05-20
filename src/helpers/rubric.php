<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/src/database/databaseHandler.php';

class Rubric {
	public string $name;
	public int $id;
	public array $rubrics = array();

	public function __construct(string $name = "__BaseRubric", int $id = -1) {
		$this->name = $name;
		$this->id = $id;
	}

	public function get(int $id): ?Rubric {
		for ($i = 0; $i < count($this->rubrics); $i++) {
			if ($this->rubrics[$i]->id === $id)
				return $this->rubrics[$i];
		}

		return null;
	}

	public function add(Rubric $rubric) {
		$this->rubrics[] = $rubric;
	}
}

class RubricHelper {
	/**
	 * Sorts the the output of the database table `Rubric`.
	 *
	 * @source https://en.wikipedia.org/wiki/Bubble_sort#Optimizing_bubble_sort
	 *
	 * @param array $arr - The array to be sorted by `rubric`.
	 */
	public static function sort(array &$arr) {
		$length = count($arr);
		$checkLength;

		while ($length > 0) {
			$checkLength = 0;

			for ($i = 1; $i < $length; $i++) {
				if ($arr[$i - 1]["rubric"] > $arr[$i]["rubric"]) {
					$temp = $arr[$i - 1];
					$arr[$i - 1] = $arr[$i];
					$arr[$i] = $temp;

					$checkLength = $i;
				}
			}

			$length = $checkLength;
		}
	}

	public static function getRubricsFromDataBase():Rubric {
		$db = new DatabaseHandler();
		$ret = '<li class="nav-item dropdown"><a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#">Alle veilingitems</a>';

		$databaseRubrics = $db->query(
			"SELECT rubric_number, rubric_name, rubric FROM Rubric;"
		);

		$baseRubrics = array_values(
			array_filter($databaseRubrics, function ($value) {
				return is_null($value["rubric"]);
			})
		);
		$rubricsRest = array_values(
			array_filter($databaseRubrics, function ($value) {
				return !is_null($value["rubric"]);
			})
		);

		RubricHelper::sort($rubricsRest);

		$rubrics = new Rubric();
		$counter = 0;
		$flatTree = array();

		foreach ($baseRubrics as $baseRubric) {
			$rubrics->add(
				new Rubric($baseRubric["rubric_name"], $baseRubric["rubric_number"])
			);
			$flatTree[] = $rubrics->get($baseRubric["rubric_number"]);
		}

		while ($counter < count($rubricsRest)) {
			$rubric = $rubricsRest[$counter++];
			$parent = $rubrics->get($rubric["rubric_number"]);

			if (!is_null($parent)) {
				$parent->add(
					new Rubric($rubric["rubric_name"], $rubric["rubric_number"])
				);
				$flatTree[] = $parent->get($rubric["rubric_number"]);
			} else {
				$reversedCounter = count($flatTree);

				while ($reversedCounter > 0) {
					$parent = $flatTree[--$reversedCounter];

					if (!is_null($parent) && $parent->id == $rubric["rubric"]) {
						$parent->add(
							new Rubric($rubric["rubric_name"], $rubric["rubric_number"])
						);
						$flatTree[] = $parent->get($rubric["rubric_number"]);
						break;
					}
				}
			}
		}
		return $rubrics;
	}
}
