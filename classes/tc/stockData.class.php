<?php
	/**
	 * data management for the stockData table
	 */
	class stockData
	{
		public $symbol;
		public $currentPrice;	// 'ask' price
		public $currentPrice2;	// 'ask' price (if exist) or 'lastTradePriceOnly'
		public $currentPrice2Src;	// 'ask' price (A) or 'lastTradePriceOnly' (L)
		public $change;		// change in price today
		public $changePercent;	// percentual change in price today
		public $yearHigh;	// 52 week high
		public $yearLow;	// 52 week low
		public $yield;		// Divident yield
		public $dps;		// Dividend payout per share
		public $xDate;
		public $pDate;		// Dividend pay date
 		public $eps;		// Earning per share
 		public $peTrailing;	// Trailing PE
		public $name;
		public $currency;       // currency of stock
		public $lastUpdated;
		public $companywebsite;
		public $errors;

		
		function __construct()
		{
			if ($_SESSION['debug'] == "on"){
				print "<span class='debug'>stockData->construct</span><br>";
			}
			
			$this->symbol = '';
			$this->currentPrice = '';
			$this->currentPrice2 = '0';
			$this->currentPrice2Src = '';
			$this->change = '';
			$this->changePercent = '';
			$this->yearHigh = '';
			$this->yearLow = '';
			$this->yield = '';
			$this->dps = '';
			$this->xDate = '';
			$this->pDate = '';
			$this->eps = '';
			$this->name = '';
			$this->peTrailing = '';
			$this->currency = '';
			$this->lastUpdated = 0;
			$this->companywebsite = '';
			$this->errors = 'none';
		}

		
		/**
		 * selects a stock's data, requires symbol
		 */
		public function select()
		{
			if ($_SESSION['debug'] == "on"){
				print "<span class='debug'>stockData->select</span><br>";
			}
			
			include_once './classes/db.class.php';
			
			if ($_SESSION['debug'] == "on"){
				print "<span class='debug'>dbConnect: stockData.class.php " . __LINE__ . "</span><br>";
			}
			
			$conn = new db();
			$conn->fileName = $_SESSION['userId'];
			$db = $conn->connect();

			$sql = "SELECT * FROM stockData WHERE symbol=:symbol";
			$rs = $db->prepare($sql);
			$rs->bindValue(':symbol', $this->symbol);
			$rs->execute();
			$rows = $rs->fetchAll();

			$currentPriceTmp = '';

			foreach($rows as $row)
			{
				if($row['attribute'] == "ask")
				{
					$this->currentPrice = $row['value'];
					$this->currentPrice2 = $row['value'];
					$this->currentPrice2Src = 'A';
				}
				elseif($row['attribute'] == "lastTradePriceOnly")
				{
					$currentPriceTmp = $row['value'];
				}
				elseif($row['attribute'] == "fiftyTwoWeekHigh")
				{
					$this->yearHigh = $row['value'];
				}
				elseif($row['attribute'] == "fiftyTwoWeekLow")
				{
					$this->yearLow = $row['value'];
				}
				elseif($row['attribute'] == "dividendYield")
				{
					$this->yield = $row['value'];
				}
				elseif($row['attribute'] == "dps")
				{
					$this->dps = $row['value'];
				}
				elseif($row['attribute'] == "exDividendDate")
				{
					$this->xDate = $row['value'];
				}
				elseif($row['attribute'] == "dividendPayDate")
				{
					$this->pDate = $row['value'];
				}
				elseif($row['attribute'] == "earningsPerShare")
				{
					$this->eps = $row['value'];
				}
				elseif($row['attribute'] == "name")
				{
					$this->name = $row['value'];
				}
				elseif($row['attribute'] == "currency")
				{
					$this->currency = $row['value'];
				}
				elseif($row['attribute'] == "peTrailing")
				{
					$this->peTrailing = $row['value'];
				}
				elseif($row['attribute'] == "change")
				{
					$this->change = $row['value'];
				}
				elseif($row['attribute'] == "changeInPercent")
				{
					$this->changePercent = $row['value'];
				}
				elseif($row['attribute'] == "website")
				{
					$this->companywebsite = $row['value'];
				}
    
				$this->lastUpdated = $row['lastUpdated'];
				
			}
			if ($this->currentPrice2 == '' || $this->currentPrice2 == '0' || $this->currentPrice2 == '0.00') {
				$this->currentPrice2 = $currentPriceTmp; // if no non-zero 'ask' value was found
				$this->currentPrice2Src = 'L';
			}
			
			if ($this->currentPrice == '') {
				$this->currentPrice = 0; // Must NEVER return blank. Used for calculations (probably only used currentPrice2 now)
			}
			if ($this->currentPrice2 == '') {
				$this->currentPrice2 = 0; // Must NEVER return blank. Used for calculations
				$this->currentPrice2Src = 'D';
			}
			if ($this->lastUpdated == '') {
				$this->lastUpdated = 0;  // Must NEVER return blank. Used for comparison
			}

			if ($_SESSION['debug'] == "on"){
				print "<span class='debug'>dbDisconnect: stockData.class.php " . __LINE__ . "</span><br>";
			}
			
			$rs   = NULL;
			$sql  = NULL;
			$db   = NULL;
			$conn = NULL;
		}

		
		/**
		 * inserts a stock's data from yahoo
		 */
		public function insert($category)
		{
			# connect to the database
			include_once './classes/db.class.php';
			 
			$conn = new db();
			$conn->fileName = $_SESSION['userId'];
			$db=$conn->connect();
		
			$sqlSettings = "SELECT * FROM settings WHERE settingName='stockdataclass'";
			$rsSettings  = $db->prepare($sqlSettings);
			$rsSettings->execute();
			$rowSettings = $rsSettings->fetch();
			$stockdataclass = $rowSettings['settingValue'];

			$yahooBaseAPI = getSettingValue('yahooAPIBaseUrl', $db);
			if ($yahooBaseAPI == '') {
				$stockdataclass = 'MOCK';
			}

			# get the stock data from yahoo
			if (strtoupper($stockdataclass) == 'MOCK')
				include_once './classes/yahoo.MOCK.class.php';
			else
				include_once './classes/yahoo2020.class.php';

			$stock = new stockdataapi();
			$stock->url = $yahooBaseAPI;
			$stock->symbol = strtoupper($this->symbol);
			$stock->getData();
			

			# load the new data in
			$sqlInsert = "INSERT INTO stockData (symbol, market, attribute, value, lastUpdated, category) ";
			$sqlInsert .= "VALUES(:symbol, '', :key, :value, :lastupdated, :category)";
			$rsInsert = $db->prepare($sqlInsert);
			foreach($stock as $key => $value)
			{
				$rsInsert->bindValue(':symbol', strtoupper($this->symbol));
				$rsInsert->bindValue(':key', $key);
				$rsInsert->bindValue(':value', $value);
				$rsInsert->bindValue(':lastupdated', time());
				$rsInsert->bindValue(':category', strtoupper($category));
				$rsInsert->execute();
			}
			
			# disconnect from the database
			$rsInsert = null;
			$db = null;
			$conn = null;
		}

		/**
		 * inserts a stock's static data from yahoo
		 */
		public function insertStaticData($category)
		{
			# connect to the database
			include_once './classes/db.class.php';
			 
			$conn = new db();
			$conn->fileName = $_SESSION['userId'];
			$db=$conn->connect();
		
			$sqlSettings = "SELECT * FROM settings WHERE settingName='stockdataclass'";
			$rsSettings  = $db->prepare($sqlSettings);
			$rsSettings->execute();
			$rowSettings = $rsSettings->fetch();
			$stockdataclass = $rowSettings['settingValue'];

			# get the stock data from yahoo
			if (strtoupper($stockdataclass) == 'MOCK')
				include_once './classes/yahoo.MOCK.class.php';
			else
				include_once './classes/yahoo2020.class.php';
			
			$stock = new stockdataapi();
			$staticData = $stock->getStaticStockData($this->symbol);
			

			# load the new data in
			$sqlInsert = "INSERT INTO stockData (symbol, market, attribute, value, lastUpdated, category) ";
			$sqlInsert .= "VALUES(:symbol, '', :key, :value, :lastupdated, :category)";
			$rsInsert = $db->prepare($sqlInsert);
			foreach($staticData as $key => $value)
			{
				$rsInsert->bindValue(':symbol', strtoupper($this->symbol));
				$rsInsert->bindValue(':key', $key);
				$rsInsert->bindValue(':value', $value);
				$rsInsert->bindValue(':lastupdated', time());
				$rsInsert->bindValue(':category', strtoupper($category));
				$rsInsert->execute();
			}
			
			# disconnect from the database
			$rsInsert = null;
			$db = null;
			$conn = null;
		}


		/**
		 * insert the current stockprice directly in the stockData table
		 */
		public function insertSimple($category)
		{
			# connect to the database
			include_once './classes/db.class.php';
			 
			$conn = new db();
			$conn->fileName = $_SESSION['userId'];
			$db=$conn->connect();

			$sqlInsert = "INSERT INTO stockData (symbol, market, attribute, value, lastUpdated, category) ";
			$sqlInsert .= "VALUES(:symbol, '', :key , :value, :lastupdated, :category)";
			$rsInsert = $db->prepare($sqlInsert);
			$rsInsert->bindValue(':symbol', strtoupper($this->symbol));
			$rsInsert->bindValue(':category', strtoupper($category));
			$rsInsert->bindValue(':lastupdated', time());

			$rsInsert->bindValue(':key', 'ask');
			$rsInsert->bindValue(':value', $this->currentPrice);
			$rsInsert->execute();

			$rsInsert->bindValue(':key', 'name');
			$rsInsert->bindValue(':value', $this->name);
			$rsInsert->execute();
		
			# disconnect from the database
			$rsInsert = null;
			$db = null;
			$conn = null;
		}
		
		/**
		 * deletes a stock's data, requires symbol
		 */
		public function delete()
		{
			# connect to the database
			include_once './classes/db.class.php';
			 
			$conn = new db();
			$conn->fileName = $_SESSION['userId'];
			$db=$conn->connect();
				
			# purge existing data for the stock
			$sqlPurge = "DELETE FROM stockData WHERE symbol=:symbol AND CanDelete='Y'";
			$rsPurge = $db->prepare($sqlPurge);
			$rsPurge->bindValue(':symbol', strtoupper($this->symbol));
			$rsPurge->execute();
			
			# disconnect from the database
			$rsPurge = null;
			$db = null;
			$conn = null;
		}
		/**
		 * deletes a stock's data for a specific category, requires symbol
		 */
		public function deleteCategory($category)
		{
			# connect to the database
			include_once './classes/db.class.php';
			 
			$conn = new db();
			$conn->fileName = $_SESSION['userId'];
			$db=$conn->connect();
				
			# purge existing data for the stock
			$sqlPurge = "DELETE FROM stockData WHERE symbol=:symbol AND UPPER(category)=:category AND CanDelete='Y'";
			$rsPurge = $db->prepare($sqlPurge);
			$rsPurge->bindValue(':symbol', strtoupper($this->symbol));
			$rsPurge->bindValue(':category', strtoupper($category));
			$rsPurge->execute();
			
			# disconnect from the database
			$rsPurge = null;
			$db = null;
			$conn = null;
		}
	}
?>
