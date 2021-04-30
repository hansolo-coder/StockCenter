<?php
	/**
	 * data management for exchangeRates
	 */
	class exchangeRates
	{
		public $errors;
		
		function __construct()
		{
			$this->errors = '';
		}

		
		/**
		 * selects the rates for (converting to) a currency
		 * The rates for the date nearest to -now- is chosen.
		 *
		 * TODO modify to calculate and return "cross-rates" if rate is not directly found
		 */
		public function select($toCurrency)
		{
			include_once './classes/db.class.php';

			if ($_SESSION['debug'] == "on"){
				print "<span class='debug'>dbConnect: exchangeRates.class.php " . __LINE__ . "</span><br>";
			}
			
			$conn = new db();
			$conn->fileName = $_SESSION['userId'];
			$db = $conn->connect();

			// TODO this should not cause rates to be refreshed on EVERY call
			$this->refresh($db);

			// Find exchangerates nearest to a given date
			// The 2 bottom now's are the date you want to find the nearest rate for
			$sql = "SELECT fromCurrency, rate FROM exchangeRates WHERE date = (SELECT fdate FROM" .	
				" (SELECT ABS(CAST((JulianDay('now') - JulianDay(fdate)) AS INTEGER)) AS DateDiff, fdate FROM" .
					" (SELECT min(date) as fdate FROM exchangerates WHERE date >= date('now')" .
					" UNION" .
					" SELECT max(date) FROM exchangerates WHERE date <= date('now')" .
					" ) dates" .
				" WHERE fdate <> '' ORDER BY 1 LIMIT 1) nearestdate" .
				" ) AND toCurrency = :toCurrency"; // ,'-1 day'
			$rs = $db->prepare($sql);
			$rs->bindValue(':toCurrency', $toCurrency);
			$rs->execute();
			$rateRows = $rs->fetchAll();
			$rates = array();
			foreach ($rateRows as $trow) {
			  $rates[$trow["fromCurrency"]] = $trow["rate"];
			}

			if ($_SESSION['debug'] == "on"){
				print "<span class='debug'>dbDisconnect: exchangeRates.class.php " . __LINE__ . "</span><br>";
			}
			
			$conn = NULL;
			$db   = NULL;
			$sql  = NULL;
			$rs   = NULL;
			$row  = NULL;

			return $rates;
		}

		
		/**
		 * refreshRates
		 *
		 * Here rates are fetched from the danish national bank which are freely available.
		 * Only direct rates are to/from DKK, but the rates can be used to give
		 * fairly accurate conversionrates for all the currencies the bank provides.
		 * ex. 2 USD->2 GBP = 2 * USD rate / GBP rate
		 *
		 * This method is implementation specific to the danish national bank, but if your own countrys
		 * national bank provide rates, it should not be a too big task to modify this.
		 */
		private function refresh($db)
		{
			$url = "https://www.nationalbanken.dk/_vti_bin/DN/DataService.svc/CurrencyRatesXML?lang=en";

			if ($_SESSION['debug'] == "on") {
				print "<span class='debug'>refresh: exchangeRates.class.php " . __LINE__ . "</span><br>";
			}
			
			$exchangeRates = "";
			try {
				$exchangeRates = @file_get_contents($url);
			} catch (Exception $e) {
				if ($_SESSION['debug'] == "on") {
					print "<span class='debug'>exception: exchangeRates.class.php " . __LINE__ . ":" . $e->getMessage() .  "</span><br>";
				}
				return array();
			}
			if ($exchangeRates === FALSE) {	
				$this->errors = "Error fetching exchange rates: " . error_get_last()['message'];
				return array();
			}

			$xml=simplexml_load_string($exchangeRates);
			$dailyratesdate = $xml->xpath("/exchangerates/dailyrates/@id");
			$refCur = $xml->xpath("/exchangerates/@refcur");
			$source = $xml->xpath("/exchangerates/@author");

			//$today = date('Y-m-d'); // \TH:i:s

			$rs = $db->prepare("SELECT count(*) as theCount FROM exchangerates WHERE date=:date");
			$rs->bindValue(':date', $dailyratesdate[0]['id']);
			$rs->execute();
			$row = $rs->fetch();

			if($row['theCount'] > 0) {
				$rs = $db->prepare("DELETE FROM exchangeRates WHERE date = :date");
				$rs->bindValue(':date', $dailyratesdate[0]['id']);
				$rs->execute();
			}

			$rs = $db->prepare("INSERT INTO exchangeRates (source, date, fromCurrency, toCurrency, rate) VALUES (:source, :date, :fromCurrency, :toCurrency, :rate)");
			$rs->bindValue(':date', $dailyratesdate[0]['id']);
			$rs->bindValue(':source', $source[0]['author']);
			$rs->bindValue(':toCurrency', $refCur[0]['refcur']);

			$rates = $xml->xpath("/exchangerates/dailyrates/currency");
			foreach ($rates as $rate) {
				$rs->bindValue(':fromCurrency', (string)($rate[0]['code']));
				$rs->bindValue(':rate', (string)($rate[0]['rate']));
				$rs->execute();
			}
			$rs->bindValue(':fromCurrency', $refCur[0]['refcur']);
			$rs->bindValue(':rate', 100.00);
			$rs->execute();

			if ($_SESSION['debug'] == "on") {
				print "<span class='debug'>cleanup: exchangeRates.class.php " . __LINE__ . "</span><br>";
			}
			
			$sql  = NULL;
			$rs   = NULL;
			$row  = NULL;
		}
		
	}
?>
