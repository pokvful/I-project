<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/settings.php";

class DatabaseHandler {
	private $connection;

	//Constructor
	public function __construct($connection) {
		$this->connection = $connection;

		$this->query(
			"SELECT * FROM Users WHERE id = :user_id",
			array(
				":user_id" => 12,
			)
		);
	}

	//Lees waarde uit van gebruikers in users tabel
	private function getUsers() {
		$sql = "SELECT * FROM users";
		$stmt = $this->connect()->query($sql);
		while ($row = $stmt->fetch()) {
			echo $row['users_firstname'] . '<br>';
		}
	}

	//Zet waarde van voor/achternaam binnen users tabel
	private function setUsers($firstname, $lastname) {
		$sql = "INSERT INTO users(users_firstname, users_lastname)";
		$stmt = $this->connect()->query($sql);
		$stmt->execute([$firstname, $lastname]);
	}

	public function query(string $query, array $params): array {

	}

	//Maak connectie met database
	protected function connect() {
		try {
			$dsn = "mysql:host=" . SETTINGS["database"]["host"] . ";dbname=" . SETTINGS["database"]["name"] . ";charset=utf8mb4";
			$this->connection = new PDO($dsn, SETTINGS["database"]["username"], SETTINGS["database"]["password"]);
			$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			die("Connection failed: " . $e->getMessage());
		}
	}
}
