--BEGIN TRANSACTION DatabatchConvert

--BEGIN TRY
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

	-- Set all the rubrics to NULL if they have a value of `-1`
	UPDATE test.dbo.Rubric
		SET rubric = NULL
			WHERE rubric = -1;

	-- Remove the ROOT rubric from the Rubrics
	DELETE FROM test.dbo.Rubric
		WHERE rubric_number < 0;

	ALTER TABLE test.dbo.Rubric
		ADD CONSTRAINT FK_Rubric FOREIGN KEY (rubric)
			REFERENCES test.dbo.Rubric(rubric_number);

	SET IDENTITY_INSERT test.dbo.Rubric OFF;

	/**
	 * Users
	 */
	SET IDENTITY_INSERT test.dbo.Question ON;

	INSERT INTO test.dbo.Question (question_number, text_question)
		VALUES (-1, '\0');

	INSERT INTO test.dbo.[User] (
		/*1*/  username,
		/*2*/  first_name,
		/*3*/  last_name,
		/*4*/  address_line1,
		/*5*/  zip_code,
		/*6*/  city,
		/*7*/  country,
		/*8*/  day_of_birth,
		/*9*/  mailbox,
		/*10*/ [password],
		/*11*/ question,
		/*12*/ answer_text,
		/*13*/ seller
	)
		SELECT
			/*1*/  Username,
			/*2*/  '\0',
			/*3*/  '\0',
			/*4*/  '\0',
			/*5*/  Postalcode,
			/*6*/  '\0',
			/*7*/  [Location],
			/*8*/  CAST('1970-01-01' AS date),
			/*9*/  '\0',
			/*10*/ '\0',
			/*11*/ -1,
			/*12*/ '\0',
			/*13*/ 0
			FROM TEMP_DataBatch.dbo.Users;
			
	SET IDENTITY_INSERT test.dbo.Question OFF;

	/**
	 * Items
	 */
	UPDATE test.dbo.[User]
		SET seller = 1
		WHERE username IN (
			SELECT Verkoper
				FROM Items
				GROUP BY Verkoper
		);

	ALTER TABLE test.dbo.Seller
		NOCHECK CONSTRAINT CK_Seller, CK_UserIsSeller, CK_ControlOptionIsValid, CK_AuthenticationSeller;

	INSERT INTO test.dbo.Seller ([user], bank, bank_account, control_option, creditcard)
		SELECT Username, '\0', '\0', '\0', '\0'
			FROM TEMP_DataBatch.dbo.Users
			WHERE Username IN (
				SELECT Verkoper
					FROM Items
					GROUP BY Verkoper
			);

	ALTER TABLE test.dbo.Seller
		CHECK CONSTRAINT CK_Seller, CK_UserIsSeller, CK_ControlOptionIsValid, CK_AuthenticationSeller;
			
	SET IDENTITY_INSERT test.dbo.Item ON;

	INSERT INTO test.dbo.Item (
		/*1*/  item_number,
		/*2*/  title,
		/*3*/  [description],
		/*4*/  starting_price,
		/*5*/  payment_method,
		/*7*/  city,
		/*8*/  country,
		/*9*/  duration,
		/*10*/ duration_start_day,
		/*11*/ duration_start_time,
		/*12*/ shipping_cost,
		/*14*/ seller,
		/*15*/ duration_end_day,
		/*16*/ duration_end_time,
		/*17*/ auction_closed
	 )
		SELECT
			/*1*/  ID,
			/*2*/  TRIM( SUBSTRING( CONCAT( Conditie, ' ', Titel ), 0, 60 ) ),
			/*3*/  Beschrijving,
			/*4*/  Prijs,
			/*5*/  '\0',
			/*7*/  '\0',
			/*8*/  Locatie,
			/*9*/  7,
			/*10*/ CAST(CURRENT_TIMESTAMP AS date),
			/*11*/ CAST(CURRENT_TIMESTAMP AS time),
			/*12*/ 0,
			/*14*/ Verkoper,
			/*15*/ DATEADD( day, 7, CAST(CURRENT_TIMESTAMP AS date) ),
			/*16*/ CAST(CURRENT_TIMESTAMP AS time),
			/*17*/ 0
			FROM TEMP_DataBatch.dbo.Items;
			
	SET IDENTITY_INSERT test.dbo.Item OFF;

	/**
	 * Item in Rubric
	 */
	INSERT INTO test.dbo.Item_in_rubric (item, rubric_at_lowest_level)
		SELECT ID, Categorie
			FROM TEMP_DataBatch.dbo.Items;

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
	
	/*PRINT N'Commiting!';
	COMMIT TRANSACTION DatabatchConvert;
END TRY
BEGIN CATCH
	--SELECT
	--	ERROR_NUMBER() AS ErrorNumber,
	--	ERROR_SEVERITY() AS ErrorSeverity,
	--	ERROR_STATE() AS ErrorState,
	--	ERROR_PROCEDURE() AS ErrorProcedure,
	--	ERROR_LINE() AS ErrorLine,
	--	ERROR_MESSAGE() AS ErrorMessage,
	--	XACT_STATE() AS XactState;

	PRINT N'Rolling back!';
	ROLLBACK TRANSACTION DatabatchConvert;

	THROW;
END CATCH;
*/