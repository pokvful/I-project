<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/settings.php";

class DatabaseHandler {
	private $connection;

	//Constructor
	public function __construct($connection) {
		$this->connection = $connection;
	}

	//Read value from users inside users table
	private function getUsers() {
		$sql = "SELECT * FROM users";
		$stmt = $this->connect()->query($sql);
		while ($row = $stmt->fetch()) {
			echo $row['users_firstname'] . '<br>';
		}
	}

	//Set value of first nad lastname inside users table
	private function setUsers($firstname, $lastname) {
		$sql = "INSERT INTO users(users_firstname, users_lastname)";
		$stmt = $this->connect()->query($sql);
		$stmt->execute([$firstname, $lastname]);
	}

	//Make connection with database
	protected function connect() {
		try {
			$dsn = "mysql:host=" . SETTINGS["database"]["host"] . ";dbname=" . SETTINGS["database"]["name"] . ";charset=utf8mb4";
			$this->connection = new PDO($dsn, SETTINGS["database"]["username"], SETTINGS["database"]["password"]);
			$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			die("Connection failed: " . $e->getMessage());
		}
	}


	protected function getUser($name) {
		$sql = "SELECT * FROM users WHERE users_firstname = ?";
		$stmt = $this->connect()->prepare($sql);
		$stmt->execute([$name]);
		$results = $stmt->fetchAll();

		return $results;
	}


	protected function createUser($firstname, $lastname) {
		$sql = "INSERT INTO users(users_firstname, users_lastname) VALUES (?. ?)";
		$stmt = $this->connect()->prepare($sql);
		$stmt->execute([$firstname . $lastname]);
	}


	protected function setUser($firstname, $lastname) {
		$sql = "UPDATE users SET (users_firstname, users_lastname) VALUES (?. ?)";
		$stmt = $this->connect()->prepare($sql);
		$stmt->execute([$firstname, $lastname]);
	}
}
