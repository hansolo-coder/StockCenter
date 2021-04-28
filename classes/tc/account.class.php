<?php
	/**
	 * data management for the account table
	 */
	class account
	{
		public $accountId;
		public $accountNumber;
		public $accountName;
		public $financialInstitution;
		public $created;
		public $closed;
		public $isPension;
		public $accountType;
		public $accountCurrency;
		
		
		function __construct()
		{
			$this->accountId = 'none';
			$this->accountNumber = 'none';
			$this->accountName = 'none';
			$this->financialInstitution = 'none';
			$this->created = 'none';
			$this->closed = 'none';
			$this->isPension = "N";
			$this->accountType = "none";
			$this->accountCurrency = "none";
		}

		
		/**
		 * selects a transaction's data
		 */
		public function select()
		{
			include_once './classes/db.class.php';

			if ($_SESSION['debug'] == "on"){
				print "<span class='debug'>dbConnect: account.class.php " . __LINE__ . "</span><br>";
			}
			
			$conn = new db();
			$conn->fileName = $_SESSION['userId'];
			$db = $conn->connect();

			$sql = "SELECT * FROM accounts WHERE accountId=:id";
			$rs = $db->prepare($sql);
			$rs->bindValue(':id', $this->accountId);
			$rs->execute();
			$row = $rs->fetch();

			$this->accountNumber = $row['accountNumber'];
			$this->accountName = $row['accountName'];
			$this->financialInstitution = $row['financialInstitution'];
			$this->created = $row['created'];
			$this->closed = $row['closed'];
			$this->isPension = $row['isPension'];
			$this->accountType = $row['accountType'];
			$this->accountCurrency = $row['accountCurrency'];

			if ($_SESSION['debug'] == "on"){
				print "<span class='debug'>dbDisconnect: account.class.php " . __LINE__ . "</span><br>";
			}
			
			$conn = NULL;
			$db   = NULL;
			$sql  = NULL;
			$rs   = NULL;
			$row  = NULL;
		}

		
		/**
		 * inserts a new account
		 */
		public function insert()
		{
			include_once './classes/db.class.php';

			if ($_SESSION['debug'] == "on"){
				print "<span class='debug'>dbConnect: account.class.php " . __LINE__ . "</span><br>";
			}
			
			$conn = new db();
			$conn->fileName = $_SESSION['userId'];
			$db = $conn->connect();

			$sql = "INSERT INTO accounts (accountNumber, accountName, financialInstitution, aCreated, aClosed, isPension, accountType, accountCurrency) VALUES(:accountNumber, :accountName, :financialInstitution, :created, :closed, :isPension, :accountType, :accountCurrency)";
			$rs = $db->prepare($sql);
			$rs->bindValue(':accountNumber', trim($this->accountNumber));
			$rs->bindValue(':accountName', trim($this->accountName));
			$rs->bindValue(':financialInstitution', trim($this->financialInstitution));
			$rs->bindValue(':created', $this->created);
			$rs->bindValue(':closed', $this->closed);
			$rs->bindValue(':isPension', $this->isPension);
			$rs->bindValue(':accountType', $this->accountType);
			$rs->bindValue(':accountCurrency', $this->accountCurrency);
			$rs->execute();

			if ($_SESSION['debug'] == "on"){
				print "<span class='debug'>dbDisconnect: account.class.php " . __LINE__ . "</span><br>";
			}
			
			$conn = NULL;
			$db   = NULL;
			$sql  = NULL;
			$rs   = NULL;
			$row  = NULL;
		}

		
		/**
		 * updates an account (unused)
		 */
		public function update()
		{

		}

		
		/**
		 * deletes an account
		 */
		public function delete()
		{
			include_once './classes/db.class.php';

			if ($_SESSION['debug'] == "on"){
				print "<span class='debug'>dbConnect: account.class.php " . __LINE__ . "</span><br>";
			}
			
			$conn = new db();
			$conn->fileName = $_SESSION['userId'];
			$db = $conn->connect();

			$sql = "DELETE FROM accounts WHERE accountId=:id";
			$rs = $db->prepare($sql);
			$rs->bindValue(':id', $this->accountId);
			$rs->execute();

			if ($_SESSION['debug'] == "on"){
				print "<span class='debug'>dbDisconnect: account.class.php " . __LINE__ . "</span><br>";
			}
			
			$conn = NULL;
			$db   = NULL;
			$sql  = NULL;
			$rs   = NULL;
		}
	}
?>
