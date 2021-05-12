<?php
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
			if ( $this->rubrics[$i]->id === $id )
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
				if ( $arr[$i - 1]["rubric"] > $arr[$i]["rubric"] ) {
					$temp = $arr[$i - 1];
					$arr[$i - 1] = $arr[$i];
					$arr[$i] = $temp;

					$checkLength = $i;
				}
			}

			$length = $checkLength;
		}
	}
}
