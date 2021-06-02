<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/api/baseHandler.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/database/databaseHandler.php';

// Set the `X-cronjob` header to this key: `$2y$10$baRj/bWBpH.mMDmXyseERevm/eyfiHmUMWPRjtNOHC7vSMq2B2XOG`

class UpdateAuctionClosedHandler extends BaseHandler {
	public function run() {
		header("Content-Type: application/json");

		try {
			$DATE_FORMAT = "Y-m-d H:i:s";
			$TIME_ZONE = new DateTimeZone("+0200");
			$dbh = new DatabaseHandler();

			$closedAuctions = array();

			$openAuctions = $dbh->query(
				<<<SQL
					SELECT item_number, duration, duration_start_day, duration_start_time
						FROM Item
						WHERE auction_closed = 0;
				SQL
			);

			foreach ($openAuctions as &$openAuction) {
				$start = new DateTime(
					$openAuction["duration_start_day"] . ' ' . $openAuction["duration_start_time"],
					$TIME_ZONE
				);
				$end = (clone $start)->add(
					new DateInterval(
						'P' . $openAuction["duration"] . 'D'
					)
				);

				$diff = $end->diff(new DateTime('NOW', $TIME_ZONE));
				// if the time is inverted, the difference between `$end` and
				// now is not greater then `$duration`
				$shouldClose = !$diff->invert;

				if ($shouldClose) {
					try {
						$dbh->query(
							<<<SQL
								UPDATE Item
									SET auction_closed = 1
									WHERE item_number = :item_number
							SQL,
							array(
								":item_number" => $openAuction["item_number"]
							)
						);

						$closedAuctions[] = $openAuction["item_number"];
					} catch (Exception $e) {
						http_response_code(500);
						die(json_encode(
								array(
									"error" => true,
									"code" => 500,
									"data" => array(
										"time" => time(),
										"message" => $e->getMessage(),
										"file" => $e->getFile(),
										"line" => $e->getLine(),
									)
								)
							));
					}
				}
			}

			http_response_code(200);
			echo json_encode(
				array(
					"error" => false,
					"code" => 200,
					"data" => array(
						"time" => time(),
						"closed_count" => count($closedAuctions),
						"closed_auctions" => $closedAuctions,
					),
				)
			);
			exit();
		} catch (Exception $e) {
			http_response_code(500);
			die(json_encode(
					array(
						"error" => true,
						"code" => 500,
						"data" => array(
							"time" => time(),
							"message" => $e->getMessage(),
							"file" => $e->getFile(),
							"line" => $e->getLine(),
						)
					)
				));
		}
	}
}
