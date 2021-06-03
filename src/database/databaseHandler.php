<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/settings.php";

/**
 * Class DatabaseHandler
 */
class DatabaseHandler {

	private PDO $connection;

	/**
	 * DatabaseHandler constructor.
	 */
	public function __construct() {
		$this->connect();
	}

	/**
	 * Read values from users inside users table.
	 */
	private function getUsers() {
		$sql = "SELECT * FROM users";
		$stmt = $this->connection->query($sql);
		while ($row = $stmt->fetch()) {
			echo $row['users_firstname'] . '<br>';
		}
	}

	/**
	 * Updates value from column inside users table.
	 *
	 * @param $firstname value of firstname from user inside users table.
	 */
	private function setUsers($firstname) {
		$sql = "UPDATE users SET users_firstname = $firstname";
		$stmt = $this->connection->query($sql);
		$stmt->execute([$firstname]);
	}

	/**
	 * Creates users inside users table.
	 *
	 * @param string $firstname value of firstname from user inside users table.
	 * @param string $lastname value of lastname from user inside users table.
	 */
	private function createUsers(string $firstname, string $lastname) {
		$sql = "INSERT INTO users(users_firstname, users_lastname) VALUES (?, ?)";
		$stmt = $this->connection->query($sql);
		$stmt->execute([$firstname, $lastname]);
	}

	private function parseResult(array $results) {
		$final = [];

		foreach ($results as $key => $result) {
			$final[$key] = array_filter($result, function ($key) {
				return gettype($key) == "string";
			}, ARRAY_FILTER_USE_KEY);
		}
		return $final;
	}

	/**
	 * Prepares, executes and fetches query.
	 *
	 * @param string $query value of query string.
	 * @param array $params key value pairs of query variables.
	 * @return array function returns array as return value.
	 */
	public function query(string $query, array $params = array()): array {
		$stmt = $this->connection->prepare($query);

		foreach ($params as $key => $value) {
			$type = NULL;

			switch (gettype($value)) {
				case 'NULL':
					$type = PDO::PARAM_NULL;
					break;
				case 'boolean':
					$type = PDO::PARAM_BOOL;
					break;
				case 'integer':
					$type = PDO::PARAM_INT;
					break;
				case 'string':
				default:
					$type = PDO::PARAM_STR;
					break;
			}

			$stmt->bindValue($key, $value, $type);
		}

		$stmt->execute();

		try {
			return $this->parseResult($stmt->fetchAll());
		} catch (Exception $e) {
			return [];
		}
	}

	/**
	 * Makes a connection with the database
	 */
	private function connect() {
		try {
			$dbh = "sqlsrv:Server=" . SETTINGS["database"]["host"] . ";Database=" . SETTINGS["database"]["name"] . ";ConnectionPooling=0";
			$this->connection = new PDO($dbh, SETTINGS["database"]["username"], SETTINGS["database"]["password"]);

			if (SETTINGS["debug"])
				$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			die("Connection failed: " . $e->getMessage());
		}
	}
}
