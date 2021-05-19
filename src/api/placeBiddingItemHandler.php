<?php

require_once $_SERVER["DOCUMENT_ROOT"] . '/src/database/databaseHandler.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/api/baseHandler.php';

class PlaceBiddingItemHandler extends BaseHandler {

	public function run() {

		$dbh = new DatabaseHandler();
		$title = $_POST["title"];
		$description = $_POST["description"];
		$shipping_cost = $_POST["shipping_cost"];
		$shipping_instruction = $_POST["shipping_instruction"];
		$selling_price = $_POST["selling_price"];
		$file_name = $_POST["file_name"];
		$item_number = $_POST["item_number"];

		$placeItemQuery = $dbh->query(
			<<<SQL 
			INSERT INTO Item (title, [description], shipping_cost, shipping_instruction, selling_price)
				VALUES(:title, :description, :shipping_cost, :shipping_instruction, :selling_price) 
		SQL,

			array(
				":title" => $title,
				":description" => $description,
				"shipping_cost" => $shipping_cost,
				":shipping_instruction" => $shipping_instruction,
				":selling_price" => $selling_price
			));

		$placeMediaQuery = $dbh->query(
			<<<SQL 
			INSERT INTO [File] ([filename], item) 
				VALUES(:file_name, :item_number) 
			SQL,

			array(
				":file_name" => $file_name,
				"item_number" => $item_number
			));
	}

}