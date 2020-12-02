<?php
	/**
	 * data management for the transaction table
	 */
	class transaction
	{
		public $accountId;
		public $tDate;
		public $symbol;
		public $activity;
		public $shares;
		public $cost;
		public $DateIsApprox;
		public $currency;
		public $tax;
		public $errors;

		
		
		function __construct()
		{
			$this->accountId = 'none';
			$this->tDate = 'none';
			$this->symbol = 'none';
			$this->activity = 'none';
			$this->shares = 'none';
			$this->cost = 'none';
			$this->DateIsApprox = 0;
			//TODO This should not be DefaultCurrency, but stocks currency....
			$this->currency = $_SESSION['DefaultCurrency'];
			$this->tax = 0;
			$this->errors = 'none';
		}

		
		/**
		 * selects a transaction's data
		 */
		public function select()
		{
			include_once './classes/db.class.php';

			if ($_SESSION['debug'] == "on"){
				print "<span class='debug'>dbConnect: transaction.class.php " . __LINE__ . "</span><br>";
			}
			
			$conn = new db();
			$conn->fileName = $_SESSION['userId'];
			$db = $conn->connect();

			$sql = "SELECT * FROM transactions WHERE symbol=:symbol";
			$rs = $db->prepare($sql);
			$rs->bindValue(':symbol', $this->symbol);
			$rs->execute();
			$row = $rs->fetch();

			$this->accountId = $row['accountId'];
			$this->tDate = $row['tDate'];
			$this->symbol = $row['symbol'];
			$this->activity = $row['activity'];
			$this->shares = $row['shares'];
			$this->cost = $row['cost'];
			$this->DateIsApprox = $row['tDateIsApprox'];
			$this->currency = $row['currency'];
			$this->tax = $row['tax'];

			if ($_SESSION['debug'] == "on"){
				print "<span class='debug'>dbDisconnect: transaction.class.php " . __LINE__ . "</span><br>";
			}
			
			$conn = NULL;
			$db   = NULL;
			$sql  = NULL;
			$rs   = NULL;
			$row  = NULL;
		}

		
		/**
		 * inserts a new transaction
		 */
		public function insert()
		{
			include_once './classes/db.class.php';

			if ($_SESSION['debug'] == "on"){
				print "<span class='debug'>dbConnect: transaction.class.php " . __LINE__ . "</span><br>";
			}
			
			$conn = new db();
			$conn->fileName = $_SESSION['userId'];
			$db = $conn->connect();

			$sql = "INSERT INTO transactions (accountId, tDate, symbol, activity, shares, cost, tDateIsApprox, currency, tax) VALUES(:accountId, :tDate, :symbol, :activity, :shares, :cost, :tDateIsApprox, :currency, :tax)";
			$rs = $db->prepare($sql);
			$rs->bindValue(':accountId', $this->accountId);
			$rs->bindValue(':tDate', $this->tDate);
			$rs->bindValue(':symbol', trim(strtoupper($this->symbol)));
			$rs->bindValue(':activity', $this->activity);
			$rs->bindValue(':shares', $this->shares);
			$rs->bindValue(':cost', $this->cost);
			$rs->bindValue(':tDateIsApprox', $this->DateIsApprox);
			$rs->bindValue(':currency', $this->currency);
			$rs->bindValue(':tax', $this->tax);
			$rs->execute();

			if ($_SESSION['debug'] == "on"){
				print "<span class='debug'>dbDisconnect: transaction.class.php " . __LINE__ . "</span><br>";
			}
			
			$conn = NULL;
			$db   = NULL;
			$sql  = NULL;
			$rs   = NULL;
			$row  = NULL;
		}

		
		/**
		 * updates a transaction (unused)
		 */
		public function update()
		{

		}

		
		/**
		 * deletes a transaction
		 */
		public function delete()
		{
			include_once './classes/db.class.php';

			if ($_SESSION['debug'] == "on"){
				print "<span class='debug'>dbConnect: transaction.class.php " . __LINE__ . "</span><br>";
			}
			
			$conn = new db();
			$conn->fileName = $_SESSION['userId'];
			$db = $conn->connect();

			$sql = "DELETE FROM transactions WHERE tDate=:tDate and symbol=:symbol and activity=:activity and shares=:shares and cost=:cost";
			$rs = $db->prepare($sql);
			$rs->bindValue(':tDate', $this->tDate);
			$rs->bindValue(':symbol', trim(strtoupper($this->symbol)));
			$rs->bindValue(':activity', $this->activity);
			$rs->bindValue(':shares', $this->shares);
			$rs->bindValue(':cost', $this->cost);
			$rs->execute();

			if ($_SESSION['debug'] == "on"){
				print "<span class='debug'>dbDisconnect: transaction.class.php " . __LINE__ . "</span><br>";
			}
			
			$conn = NULL;
			$db   = NULL;
			$sql  = NULL;
			$rs   = NULL;
		}
	}
?>
