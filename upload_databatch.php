<?php
$executable = array_shift($argv);
$executablePath = substr($executable, 0, strrpos($executable, DIRECTORY_SEPARATOR) + 1);
$files = $argv;

const HOST = "localhost";
const USERNAME = "admin_iproject";
const PASSWORD = 'EenBeterWachtwoord!$';
$TEMP_DATABASE = "TEMP_DataBatch";
const DATABASE = "test";

$conn = null;

try {
	$dbh = "sqlsrv:Server=" . HOST . ";ConnectionPooling=0";
	$conn = new PDO($dbh, USERNAME, PASSWORD);

	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	echo "Connected to database" . PHP_EOL;
} catch (PDOException $e) {
	die("Connection failed: " . $e->getMessage());
}

try {
	$conn->exec("CREATE DATABASE $TEMP_DATABASE");
	$conn->exec("ALTER DATABASE $TEMP_DATABASE SET AUTO_CLOSE OFF"); // https://dba.stackexchange.com/a/134676

	$conn->beginTransaction();

	$conn->exec(
		<<<SQL
		CREATE TABLE {$TEMP_DATABASE}.dbo.Users (
			Username VARCHAR(200),
			Postalcode VARCHAR(9),
			Location VARCHAR(MAX),
			Country VARCHAR(100),
			Rating NUMERIC(4,1)
		)
		SQL
	);

	$conn->exec(
		<<<SQL
		CREATE TABLE {$TEMP_DATABASE}.dbo.Categorieen (
			ID int NOT NULL,
			Name varchar(100) NULL,
			Parent int NULL,
			CONSTRAINT PK_Categorieen PRIMARY KEY (ID)
		)
		SQL
	);

	$conn->exec(
		<<<SQL
		CREATE TABLE {$TEMP_DATABASE}.dbo.Items (
			ID bigint NOT NULL,
			Titel varchar(max) NULL,
			Beschrijving nvarchar(max) NULL,
			Categorie int NULL,
			Postcode varchar(max) NULL,
			Locatie varchar(max) NULL,
			Land varchar(max) NULL,
			Verkoper varchar(max) NULL,
			Prijs varchar(max) NULL,
			Valuta varchar(max) NULL,
			Conditie varchar(max) NULL,
			Thumbnail varchar(max) NULL,
			CONSTRAINT PK_Items PRIMARY KEY (ID),
			CONSTRAINT FK_Items_In_Categorie FOREIGN KEY (Categorie) REFERENCES {$TEMP_DATABASE}.dbo.Categorieen (ID)
		)
		SQL
	);

	$conn->exec(
		<<<SQL
		CREATE TABLE {$TEMP_DATABASE}.dbo.Illustraties (
			ItemID bigint NOT NULL,
			IllustratieFile varchar(100) NOT NULL,
			CONSTRAINT PK_ItemPlaatjes PRIMARY KEY (ItemID, IllustratieFile),
			CONSTRAINT [ItemsVoorPlaatje] FOREIGN KEY(ItemID) REFERENCES {$TEMP_DATABASE}.dbo.Items (ID)
		)
		SQL
	);

	$conn->exec("CREATE INDEX IX_Items_Categorie ON {$TEMP_DATABASE}.dbo.Items (Categorie)");
	$conn->exec("CREATE INDEX IX_Categorieen_Parent ON {$TEMP_DATABASE}.dbo.Categorieen (Parent)");

	$conn->exec("USE $TEMP_DATABASE");

	for ($i = 0; $i < count($files); ++$i) {
		$file = $files[$i];
		echo "Executing file \"$file\"" . PHP_EOL;

		$fileContents = file_get_contents($file);

		$result = $conn->exec(
			mb_convert_encoding(
				$fileContents,
				'utf-8',
				mb_detect_encoding($fileContents)
			)
		);

		if ($result !== false) {
			$res = $conn->query("SELECT COUNT(*) AS 'rows' FROM {$TEMP_DATABASE}.dbo.Items");

			echo "Successfully executed file \"$file\". Now " . $res->fetchAll()[0]["rows"] . " rows" . PHP_EOL;
		} else {
			throw new Exception(
				"An error occurred while processing file \"$file\": "
					. ($conn->errorInfo()[2] ?? "no error information")
			);
		}
	}

	$convertScript = file_get_contents($executablePath . "databatch_convert.sql");

	$result = $conn->exec($convertScript);

	var_dump( $conn->errorInfo() );

	if ($result === false) {
		throw new Exception(
			"An error occurred while executing the convert script: "
				. ($conn->errorInfo()[2] ?? "no error information")
		);
	}
} catch (Exception $e) {
	echo "Something went wrong, rolling back" . PHP_EOL;
	echo $e->getMessage() . PHP_EOL;

	if ($conn->inTransaction())
		$conn->rollBack();

	$conn->query("USE master");
	$conn->query("DROP DATABASE $TEMP_DATABASE");

	die();
}

if ($conn->inTransaction())
	$conn->commit();

$conn->query("USE master");
$conn->query("DROP DATABASE $TEMP_DATABASE");

echo "Successfully converted all the data";

$conn = null;
