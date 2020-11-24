<?php
	/**
	 * data management for the stocks table
	 */
	class stocks
	{
		public $symbol;
		public $errors;

		
		
		function __construct()
		{
			$this->symbol = '';
			$this->errors = 'none';
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

			$sql = "SELECT * FROM stocks WHERE symbol=:symbol";
			$rs = $db->prepare($sql);
			$rs->bindValue(':symbol', $this->symbol);
			$rs->execute();
			$row = $rs->fetch();
			$this->symbol = $row['symbol'];

			if ($_SESSION['debug'] == "on"){
				print "<span class='debug'>dbDisconnectonnect: stocks.class.php " . __LINE__ . "</span><br>";
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
			
			$conn = new db();
			$conn->fileName = $_SESSION['userId'];
			$db = $conn->connect();

			$sql = "SELECT count(*) as theCount FROM stocks WHERE symbol=:symbol";
			$rs = $db->prepare($sql);
			$rs->bindValue(':symbol', $this->symbol);
			$rs->execute();
			$row = $rs->fetch();

			if($row['theCount'] == 0)
			{
				$sql = "INSERT INTO stocks (symbol) VALUES(:symbol)";
				$rs = $db->prepare($sql);
				$rs->bindValue(':symbol', trim(strtoupper($this->symbol)));
				$rs->execute();
			}
			else
			{
				$this->errors = "Record Already Exists, Cannot Insert Record";
			}

			if ($_SESSION['debug'] == "on"){
				print "<span class='debug'>dbDisconnectonnect: stocks.class.php " . __LINE__ . "</span><br>";
			}
			
			$conn = NULL;
			$db   = NULL;
			$sql  = NULL;
			$rs   = NULL;
			$row  = NULL;
		}

		
		/**
		 * updates a stock (unused)
		 */
		public function update()
		{

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
			
			$conn = new db();
			$conn->fileName = $_SESSION['userId'];
			$db = $conn->connect();

			$sql = "DELETE FROM stocks WHERE symbol=:symbol";
			$rs = $db->prepare($sql);
			$rs->bindValue(':symbol', $this->symbol);
			$rs->execute();

			if ($_SESSION['debug'] == "on"){
				print "<span class='debug'>dbDisconnectonnect: stocks.class.php " . __LINE__ . "</span><br>";
			}
			
			$conn = NULL;
			$db   = NULL;
			$sql  = NULL;
			$rs   = NULL;
		}
	}
?>