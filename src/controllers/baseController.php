<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/settings.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/database/databaseHandler.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/helpers/rubric.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';

class NotImplementedException extends Exception {
}

class BaseController {
	private Latte\Engine $latteEngine;

	public string $requestPath;
	public string $filePath;
	public string $fileName;
	public array $data;

	public function __construct(string $requestPath, string $filePath, string $fileName) {
		$this->latteEngine = new Latte\Engine;

		$this->latteEngine->setTempDirectory(SETTINGS["latte"]["tempDirectory"]);

		$this->latteEngine->addFunction('getCsrfInput', function (): string {
			return "<input type=\"hidden\" name=\"csrf-token\" value=\"{$_SESSION["csrf-token"]}\" />";
		});

		$this->latteEngine->addFunction('getBreadCrumbs', function (): string {
			$newPaths = array_filter(explode('/', $this->requestPath));
			$currentPath = "/";
			$i = 0;
			$result = "<ul class=\"breadcrumb\">";

			foreach ($newPaths as $path) {
				$currentPath .= "{$path}/";
				$text = htmlentities(urldecode($path), ENT_QUOTES);

				if ($i === count($newPaths) - 1) {
					$result .= "<li class=\"breadcrumb-item active\"> {$text} </li>";
				} else {
					$result .= "<li class=\"breadcrumb-item\"> <a href=\"{$currentPath}\"> {$text} </a></li>";
				}
				$i++;
			}

			$result .= "</ul>";

			return $result;
		});

		$this->latteEngine->addFunction('getRubricTree', function (): string {
			// <li class="nav-item dropdown">
			// 	<a class=" nav-link dropdown-toggle" data-toggle="dropdown" href="#">Alle veilingitems</a>
			// 	<div class="px-4 dropdown-menu text-center" id="dropdown-menu-placement">
			// 		<a class="dropdown-item">Speelgoed</a>
			// 		<a class="dropdown-item">Fietsen</a>
			// 		<a class="dropdown-item">Laptops</a>
			// 	</div>
			// </li>

			$db = new DatabaseHandler();

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

			bdump($flatTree, 'flat tree');
			bdump($rubrics, 'rubrics');

			$ret = '<li class="nav-item dropdown"><a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#">Alle veilingitems</a>';
			$ret .= '</li>';

			return $ret;
		});

		$this->requestPath = $requestPath;
		$this->filePath = $filePath;
		$this->fileName = $fileName;
		$this->data = array(
			"_requestPath" => urldecode($requestPath),
			"_filePath" => urldecode($filePath),
			"_fileName" => urldecode($fileName),
			"_params" => $_GET,
			"_csrfToken" => $_SESSION["csrf-token"],
			"_loggedin" => $_SESSION["loggedin"] ?? false,
			"_username" => $_SESSION["username"] ?? null,
			"_admin" => $_SESSION["admin"] ?? false,
		);
	}

	/**
	 * Redirect the user to a url
	 *
	 * @param string $path The url the user should be send to
	 */
	protected function redirect(string $path) {
		header("Location: " . $path);
		exit();
	}

	/**
	 * Render the template
	 */
	protected function render() {
		$this->latteEngine->render(
			$_SERVER["DOCUMENT_ROOT"] . "/src/views/"
			. $this->filePath . "{$this->fileName}.latte",
			$this->data,
		);
	}

	/**
	 * @throws NotImplementedException
	 */
	public function run() {
		throw new NotImplementedException(
			"Controller \"/src/controllers{$this->filePath}{$this->fileName}\""
			. " hasn't been implemented"
		);
	}
}
