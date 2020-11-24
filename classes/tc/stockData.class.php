<?php
	/**
	 * data management for the stockData table
	 */
	class stockData
	{
		public $symbol;
		public $currentPrice;
		public $yearHigh;
		public $yearLow;
		public $yield;
		public $dps;
		public $xDate;
		public $pDate;
		public $eps;
		public $name;
		public $lastUpdated;
		public $errors;

		
		function __construct()
		{
			if ($_SESSION['debug'] == "on"){
				print "<span class='debug'>stockData->construct</span><br>";
			}
			
			$this->symbol = '';
			$this->currentPrice = '';
			$this->yearHigh = '';
			$this->yearLow = '';
			$this->yield = '';
			$this->dps = '';
			$this->xDate = '';
			$this->pDate = '';
			$this->eps = '';
			$this->name = '';
			$this->lastUpdated = '';
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

			foreach($rows as $row)
			{
				if($row['attribute'] == "ask")
				{
					$this->currentPrice = $row['value'];
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
    
				$this->lastUpdated = $row['lastUpdated'];
				
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
		public function insert()
		{
			# get the stock data from yahoo
			include_once './classes/yahoo.class.php';
			
			$stock = new yahoo();
			$stock->symbol = strtoupper($this->symbol);
			$stock->getData();
			
			# connect to the database
			include_once './classes/db.class.php';
			 
			$conn = new db();
			$conn->fileName = $_SESSION['userId'];
			$db=$conn->connect();
			
			# load the new data in
			foreach($stock as $key => $value)
			{
				$sqlInsert = "INSERT INTO stockData (symbol, market, attribute, value, lastUpdated) ";
				$sqlInsert .= "VALUES('" . strtoupper($this->symbol) . "','','" . $key . "','" . $value . "','" . time() . "')";
				$rsInsert = $db->prepare($sqlInsert);
				$rsInsert->execute();
			}
			
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
			$sqlPurge = "DELETE FROM stockData WHERE symbol='" . strtoupper($this->symbol) . "'";
			$rsPurge = $db->prepare($sqlPurge);
			$rsPurge->execute();
			
			# disconnect from the database
			$rsPurge = null;
			$db = null;
			$conn = null;
		}
	}
?>