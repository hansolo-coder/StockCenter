<?php
	/**
	 * data management for the stocks table
	 */
	class stocks
	{
		public $symbol;
		public $symbolId;
		public $ISIN;
		public $name;
		public $url;
		public $skipLookup;
		public $errors;
		public $inError;
		
		function __construct()
		{
			$this->symbol = '';
			$this->symbolId = 0;
			$this->ISIN = '';
			$this->name = '';
			$this->url  = '';
			$this->skipLookup = 0; // 1 -> skip lookup in stock api - probably because it does not exist anymore
			$this->errors = 'none';
			$this->inError = FALSE;
		}

		
		/**
		 * selects a stock
		 */
		public function select()
		{
			include_once './classes/db.class.php';

			if ($_SESSION['debug'] == "on"){
				print "<span class='debug'>dbConnect: stocks.class.php " . __LINE__ . "</span><br>";
			}
			
			$conn = new db();
			$conn->fileName = $_SESSION['userId'];
			$db = $conn->connect();

			if ($this->symbolId > 0) {
			  $valueLookedUp = $this->symbolId;
			  $sql = "SELECT * FROM stocks WHERE symbolId=:symbolId";
			  $rs = $db->prepare($sql);
			  $rs->bindValue(':symbolId', $this->symbolId);
			  $rs->execute();
			  $row = $rs->fetch();
			  if ($valueLookedUp != $row['symbolId'])
			    $this->inError = TRUE;
			} else {
			  $valueLookedUp = $this->symbol;
			  $sql = "SELECT * FROM stocks WHERE symbol=:symbol";
			  $rs = $db->prepare($sql);
			  $rs->bindValue(':symbol', $this->symbol);
			  $rs->execute();
			  $row = $rs->fetch();
			  if ($valueLookedUp != $row['symbol'])
			    $this->inError = TRUE;
			}
			$this->symbolId = trim($row['symbolId']);
			$this->symbol = trim($row['symbol']);
			$this->ISIN = trim($row['ISIN']);
			$this->name = trim($row['name']);
			$this->url  = trim($row['URL']);
			$this->skipLookup = (int)$row['SkipLookup'];

			if ($_SESSION['debug'] == "on"){
				print "<span class='debug'>dbDisconnect: stocks.class.php " . __LINE__ . "</span><br>";
			}
			
			$conn = NULL;
			$db   = NULL;
			$sql  = NULL;
			$rs   = NULL;
			$row  = NULL;
		}

		
		/**
		 * inserts a stock
		 */
		public function insert()
		{
			include_once './classes/db.class.php';

			if ($_SESSION['debug'] == "on"){
				print "<span class='debug'>dbConnect: stocks.class.php " . __LINE__ . "</span><br>";
			}
			
			$success = true;

			$conn = new db();
			$conn->fileName = $_SESSION['userId'];
			$db = $conn->connect();

			$sql = "SELECT count(*) as theCount FROM stocks WHERE symbol=:symbol";
			$rs = $db->prepare($sql);
			$rs->bindValue(':symbol', $this->symbol);
			$rs->execute();
			$row = $rs->fetch(); // TODO This can be fetched easier with 'fetchValue'

			if($row['theCount'] == 0)
			{
				$sql = "INSERT INTO stocks (symbol, ISIN, name, URL, SkipLookup) VALUES(:symbol, :ISIN, :name, :url, :skipLookup)";
				$rs = $db->prepare($sql);
				$rs->bindValue(':symbol', trim(strtoupper($this->symbol)));
				$rs->bindValue(':ISIN', trim(strtoupper($this->ISIN)));
				$rs->bindValue(':name', trim($this->name));
				$rs->bindValue(':url', trim($this->url));
				$rs->bindValue(':skipLookup', $this->skipLookup);
				$rs->execute();
			}
			else
			{
				$this->errors = "Record Already Exists, Cannot Insert Record";
				$success = false;
			}

			if ($_SESSION['debug'] == "on"){
				print "<span class='debug'>dbDisconnectonnect: stocks.class.php " . __LINE__ . "</span><br>";
			}
			
			$conn = NULL;
			$db   = NULL;
			$sql  = NULL;
			$rs   = NULL;
			$row  = NULL;

			return $success;
		}

		
		/**
		 * updates a stock
		 */
		public function update()
		{
			include_once './classes/db.class.php';

			if ($_SESSION['debug'] == "on"){
				print "<span class='debug'>dbConnect: stocks.class.php " . __LINE__ . "</span><br>";
			}

			$success = true;
			
			$conn = new db();
			$conn->fileName = $_SESSION['userId'];
			$db = $conn->connect();

			if ($this->symbolId > 0) {
			  $sql = "UPDATE stocks SET symbol=:symbol, ISIN=:ISIN, name=:name, URL=:url, SkipLookup=:skipLookup WHERE symbolId=:symbolId";
			  $rs = $db->prepare($sql);
			  $rs->bindValue(':symbolId', $this->symbolId);
			} else {
			  $sql = "UPDATE stocks SET ISIN=:ISIN, name=:name, URL=:url, SkipLookup=:skipLookup WHERE symbol=:symbol";
			  $rs = $db->prepare($sql);
			}
			$rs->bindValue(':symbol', trim($this->symbol));
			$rs->bindValue(':ISIN', trim(strtoupper($this->ISIN)));
			$rs->bindValue(':name', trim($this->name));
			$rs->bindValue(':url', trim($this->url));
			$rs->bindValue(':skipLookup', $this->skipLookup);
			$rs->execute();

			if ($_SESSION['debug'] == "on"){
				print "<span class='debug'>dbDisconnect: stocks.class.php " . __LINE__ . "</span><br>";
			}
			
			$conn = NULL;
			$db   = NULL;
			$sql  = NULL;
			$rs   = NULL;

			return $success;
		}

		
		/**
		 * deletes a stock
		 */
		public function delete()
		{
			include_once './classes/db.class.php';

			if ($_SESSION['debug'] == "on"){
				print "<span class='debug'>dbConnect: stocks.class.php " . __LINE__ . "</span><br>";
			}

			$success = true;
			
			$conn = new db();
			$conn->fileName = $_SESSION['userId'];
			$db = $conn->connect();

			if ($this->symbolId > 0) {
			  $sql = "DELETE FROM stocks WHERE symbolId=:symbolId";
			  $rs = $db->prepare($sql);
			  $rs->bindValue(':symbolId', $this->symbolId);
			} else {
			  $sql = "DELETE FROM stocks WHERE symbol=:symbol";
			  $rs = $db->prepare($sql);
			  $rs->bindValue(':symbol', $this->symbol);
			}
			$rs->execute();

			if ($_SESSION['debug'] == "on"){
				print "<span class='debug'>dbDisconnect: stocks.class.php " . __LINE__ . "</span><br>";
			}
			
			$conn = NULL;
			$db   = NULL;
			$sql  = NULL;
			$rs   = NULL;

			return $success;
		}
	}
?>
