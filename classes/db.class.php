<?php
	/**
	 * database connection and initialization class
	 */
	class db
	{
		/**
		 * the filename for the database
		 * @var string
		 */
		public $fileName;
		
		/**
		 * the password for the database
		 * @var string
		 */
		public $password;


		
		function __construct()
		{
			$this->fileName = NULL;
			$this->password = NULL;
		}


		/**
		 * connects to the database
		 */
		function connect()
		{
			$dataFile = "sqlite:./data/" . $this->fileName . ".sqlite";

			# create and initialize database
			$db = new PDO($dataFile);
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			return $db;
		}

		/**
		 * Add a setting to the settings table
		 */
		function addSetting($db, $sql)
		{
			$rs = $db->prepare($sql);
			$rs->execute();
		}

		/**
		 * initializes a new database
		 */
		function init()
		{
			if ($_SESSION['debug'] == "on"){print "<span class='debug'>db->init()</span><br>";}

			# if the data directory does not exist...
			if (!file_exists("./data"))
			{
				# create it
				mkdir("./data");
			}

			# create and initialize database
			$db = new PDO("sqlite:./data/$this->fileName.sqlite");
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			# stock data table
			$sql = "CREATE TABLE [stockData] (";
			$sql .= "[symbol] VARCHAR(20),";
			$sql .= "[market] VARCHAR(10),";
			$sql .= "[attribute] VARCHAR(100),";
			$sql .= "[value] VARCHAR(200),";
			$sql .= "[lastUpdated] INTEGER)";
			$rs = $db->prepare($sql);
			$rs->execute();

			# accounts table
			$sql = "CREATE TABLE [accounts] (";
			$sql .= "[accountId] INTEGER PRIMARY KEY,";
			$sql .= "[accountNumber] varchar(20),";
			$sql .= "[accountName] varchar(20),";
			$sql .= "[financialInstitution] varchar(40),";
			$sql .= "[isPension] char(1) NOT NULL DEFAULT 'N',";
			$sql .= "[accountType] varchar(20),";
			$sql .= "[accountCurrency] VARCHAR(3) NOT NULL DEFAULT 'DKK',";
			$sql .= "[aCreated] DATE,";
			$sql .= "[aClosed] DATE)";
			$rs = $db->prepare($sql);
			$rs->execute();

			# stocks table
			$sql = "CREATE TABLE [stocks] ([symbolId] INTEGER PRIMARY KEY, [symbol] varchar(20), [ISIN] varchar(20), [name] varchar(50), SkipLookup BIT NOT NULL DEFAULT 0)";
			$rs = $db->prepare($sql);
			$rs->execute();

			# transaction table
			$sql = "CREATE TABLE [transactions] ([tDate] DATE NOT NULL,[symbol] VARCHAR(20) NOT NULL,[activity] VARCHAR(10) NOT NULL,[shares] INT,[cost] INT(0, 2), [tDateIsApprox] INTEGER, [accountId] INTEGER NOT NULL, [currency] VARCHAR(3) NOT NULL DEFAULT 'DKK', [tax] DECIMAL, exchangeRate DECIMAL)";
			$rs = $db->prepare($sql);
			$rs->execute();

			# settings table
			$sql = "CREATE TABLE [settings] ([settingName] VARCHAR(100),settingValue VARCHAR(100),settingDesc VARCHAR(100))";
			$rs = $db->prepare($sql);
			$rs->execute();

			$sql = "INSERT INTO settings (settingName, settingValue, settingDesc) VALUES('sellTrigger', '.25', 'Decimal growth percentage to signal possible sale')";
			$rs = $db->prepare($sql);
			$rs->execute();

			addSetting($db, "INSERT INTO settings (settingName, settingValue) VALUES('password', '" . md5($this->password) . "')");
			addSetting($db, "INSERT INTO settings (settingName, settingValue, settingDesc) VALUES('refreshTime', '15', 'Time between yahoo data refreshes')");
			addSetting($db, "INSERT INTO settings (settingName, settingValue, settingDesc) VALUES('databaseVersion', '3', 'Database schema version')");
			addSetting($db, "INSERT INTO settings (settingName, settingValue, settingDesc) VALUES('stockdataclass', 'yahoo.class.php', 'PHP class to handle the stock API')");
			addSetting($db, "INSERT INTO settings (settingName, settingValue, settingDesc) VALUES('currency', '$', 'Default currency symbol')");
			addSetting($db, "INSERT INTO settings (settingName, settingValue, settingDesc) VALUES('showTransactionTax', '0', 'Yes/No > Show tax on transactionlist')");
			addSetting($db, "INSERT INTO settings (settingName, settingValue, settingDesc) VALUES('region', 'EUR', 'US/EUR > Adapt to region')");
			addSetting($db, "INSERT INTO settings (settingName, settingValue, settingDesc) VALUES('chgPctMarkUnchanged', '0.2', 'Mark as unchanged if change in value is below percentage')");

		}
	}
?>
