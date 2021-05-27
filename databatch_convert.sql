BEGIN TRANSACTION SendHelp

SET IDENTITY_INSERT test.dbo.Rubric ON;

/**
 * Rubrics
 */
ALTER TABLE test.dbo.Rubric
	DROP CONSTRAINT FK_Rubric;

DECLARE @maxRubric INT;
DECLARE @faulty TABLE (
	rubric_number INT NOT NULL,
	rubric_name VARCHAR(MAX) NOT NULL,
	rubric INT NULL,
	id INT NOT NULL
);

-- Store all the rubrics which don't have a parent in the @faulty table
INSERT INTO @faulty (rubric_number, rubric_name, rubric, id)
	SELECT ID, [Name], Parent, 0
		FROM TEMP_DataBatch.dbo.Categorieen
		WHERE Parent NOT IN (
			SELECT ID
				FROM TEMP_DataBatch.dbo.Categorieen
		);

-- Get an unused rubric number
SELECT @maxRubric = MAX(ID)
	FROM TEMP_DataBatch.dbo.Categorieen;

-- Store all the faulty rubrics under the rubric 'Overig'
INSERT INTO test.dbo.Rubric (rubric_number, rubric_name, rubric, id)
	VALUES (@maxRubric + 1, 'Overig', NULL, 0);

INSERT INTO test.dbo.Rubric (rubric_number, rubric_name, rubric, id)
	SELECT rubric_number, rubric_name, @maxRubric + 1 AS 'rubric', 0 AS 'following_number'
		FROM @faulty;

-- Delete the faulty rubrics from our databatch
DELETE FROM TEMP_DataBatch.dbo.Categorieen
	WHERE ID IN (
		SELECT rubric_number
			FROM @faulty
	);

-- Insert the remaining rubrics into the table
INSERT INTO test.dbo.Rubric (rubric_number, rubric_name, rubric, id)
	SELECT ID, [Name], Parent, 0
		FROM TEMP_DataBatch.dbo.Categorieen;

-- Set all the 
UPDATE test.dbo.Rubric
	SET rubric = NULL
		WHERE rubric = -1;

DELETE FROM test.dbo.Rubric
	WHERE rubric_number < 0;

ALTER TABLE test.dbo.Rubric
	ADD CONSTRAINT FK_Rubric FOREIGN KEY (rubric)
		REFERENCES test.dbo.Rubric(rubric_number);

SET IDENTITY_INSERT test.dbo.Rubric OFF;

/**
 * Items
 */


/**
 * Pictures
 */
INSERT INTO test.dbo.[File] (item, [filename])
	SELECT ItemID, IllustratieFile
		FROM (
			SELECT
				ItemID,
				ROW_NUMBER() OVER( PARTITION BY ItemID ORDER BY ItemID ASC, IllustratieFile ASC ) AS 'row',
				IllustratieFile
				FROM TEMP_DataBatch.dbo.Illustraties
		) AS Files
		WHERE [row] < 5;
