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
			if (is_readable("index.php")) {
				$dataFile = "sqlite:./data/" . $this->fileName . ".sqlite";
			} else if (is_readable("../index.php")) {
				$dataFile = "sqlite:../data/" . $this->fileName . ".sqlite";
			} else {
				echo "ERROR: Database cannot be found";
				exit();
			}

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
			$sql .= "[symbol] VARCHAR(20) NOT NULL,";
			$sql .= "[market] VARCHAR(10),";
			$sql .= "[attribute] VARCHAR(100) NOT NULL,";
			$sql .= "[value] VARCHAR(200) NOT NULL,";
			$sql .= "[lastUpdated] INTEGER NOT NULL,";
			$sql .= "[maxage] INTEGER NOT NULL DEFAULT 0,"; // Information from API regarding maxage of this information
			$sql .= "[category] VARCHAR(10) NOT NULL DEFAULT '',"; // 'Category' (API) from which this information originates
			$sql .= "[CanDelete] char(1) NOT NULL DEFAULT 'Y')"; // 'N': Do not delete row
			$rs = $db->prepare($sql);
			$rs->execute();

			# accounts table
			$sql = "CREATE TABLE [accounts] (";
			$sql .= "[accountId] INTEGER PRIMARY KEY,";
			$sql .= "[accountNumber] varchar(20) NOT NULL,";
			$sql .= "[accountName] varchar(20) NOT NULL,";
			$sql .= "[financialInstitution] varchar(40),";
			$sql .= "[isPension] char(1) NOT NULL DEFAULT 'N',";
			$sql .= "[accountType] varchar(20),";
			$sql .= "[accountCurrency] VARCHAR(3) NOT NULL,";
			$sql .= "[aCreated] DATE,";
			$sql .= "[aClosed] DATE)";
			$rs = $db->prepare($sql);
			$rs->execute();

			# stocks table
			$sql = "CREATE TABLE [stocks] (";
			$sql .= "[symbolId] INTEGER PRIMARY KEY,";
			$sql .= "[symbol] varchar(20),";
			$sql .= "[ISIN] varchar(20),";
			$sql .= "[name] varchar(100),";
			$sql .= "[URL] varchar(500) DEFAULT '',";
			$sql .= "[SkipLookup] BIT NOT NULL DEFAULT 0)";
			$rs = $db->prepare($sql);
			$rs->execute();

			# transaction table
			$sql = "CREATE TABLE [transactions] (";
                        $sql .= "[transactionId] INTEGER PRIMARY KEY,";
			$sql .= "[accountId] INTEGER NOT NULL,";
			$sql .= "[tDate] DATE NOT NULL,";
			$sql .= "[symbol] VARCHAR(20) NOT NULL,";
			$sql .= "[activity] VARCHAR(10) NOT NULL,";
			$sql .= "[shares] INT,[cost] INT(0, 2) NOT NULL,";
			$sql .= "[tDateIsApprox] INTEGER NOT NULL DEFAULT 0,";
			$sql .= "[currency] VARCHAR(3) NOT NULL,";
			$sql .= "[tax] DECIMAL,";
			$sql .= "[exchangeRate] DECIMAL NOT NULL DEFAULT 1.0)";
			$rs = $db->prepare($sql);
			$rs->execute();

			# settings table
			$sql = "CREATE TABLE [settings] ([settingName] VARCHAR(100),settingValue VARCHAR(100),settingDesc VARCHAR(100))";
			$rs = $db->prepare($sql);
			$rs->execute();

			$sql = "INSERT INTO settings (settingName, settingValue, settingDesc) VALUES('sellTrigger', '.25', 'Decimal growth percentage to signal possible sale')";
			$rs = $db->prepare($sql);
			$rs->execute();

			$this->addSetting($db, "INSERT INTO settings (settingName, settingValue) VALUES('password', '" . md5($this->password) . "')");
			$this->addSetting($db, "INSERT INTO settings (settingName, settingValue, settingDesc) VALUES('refreshTime', '15', 'Time between yahoo data refreshes')");
			$this->addSetting($db, "INSERT INTO settings (settingName, settingValue, settingDesc) VALUES('databaseVersion', '3', 'Database schema version')");
			$this->addSetting($db, "INSERT INTO settings (settingName, settingValue, settingDesc) VALUES('stockdataclass', 'REAL', 'PHP class to handle the stock API')");
			$this->addSetting($db, "INSERT INTO settings (settingName, settingValue, settingDesc) VALUES('currency', 'EUR', 'Default currency symbol')");
			$this->addSetting($db, "INSERT INTO settings (settingName, settingValue, settingDesc) VALUES('showTransactionTax', '0', 'Yes/No > Show tax on transactionlist')");
			$this->addSetting($db, "INSERT INTO settings (settingName, settingValue, settingDesc) VALUES('region', 'EUR', 'US/EUR > Adapt to region')");
			$this->addSetting($db, "INSERT INTO settings (settingName, settingValue, settingDesc) VALUES('chgPctMarkUnchanged', '0.2', 'Mark as unchanged if change in value is below percentage')");
			// TODO md5sum this
			$this->addSetting($db, "INSERT INTO settings (settingName, settingValue, settingDesc) VALUES('accessKey', null, 'Key for authorizing remote access')");
			// TODO classes doing deletes should take this into account
			$this->addSetting($db, "INSERT INTO settings (settingName, settingValue, settingDesc) VALUES('enableDeletes', 'No', 'Enable deletion of accounts and stocks')");
			// base url for Yahoo finance website
			$this->addSetting($db, "INSERT INTO settings (settingName, settingValue, settingDesc) VALUES('yahooFinanceBaseUrl', 'https://finance.yahoo.com/quote/{}?p={}&.tsrc=fin-srch', 'Base Url for showing Yahoo finance information for stock')");
			$this->addSetting($db, "INSERT INTO settings (settingName, settingValue, settingDesc) VALUES('yahooAPIBaseUrl', 'https://query2.finance.yahoo.com/v11/finance/quoteSummary/', 'Base Url for Yahoo finance API')");

			# dailystatus table
			$sql = "CREATE TABLE [dailystatus] (";
			$sql .= " [tDate] DATE NOT NULL";
			$sql .= ",[accountId] INTEGER";
			$sql .= ",[symbol] VARCHAR(20) NOT NULL";
			$sql .= ",[shares] INT NOT NULL,[cost] DECIMAL NOT NULL";
			$sql .= ",[currency] VARCHAR(3) NOT NULL DEFAULT 'DKK'";
			$sql .= ")";
			$rs = $db->prepare($sql);
			$rs->execute();

			# ExchangeRates table
			$sql = "CREATE TABLE [exchangeRates] (";
			$sql .= "  [source] VARCHAR(50),";
			$sql .= "  [date] VARCHAR(10),";
			$sql .= "  [fromCurrency] VARCHAR(5),";
			$sql .= "  [toCurrency] VARCHAR(5),";
			$sql .= "  [rate] DECIMAL(25,8)";
			$sql .= ")";
			$rs = $db->prepare($sql);
			$rs->execute();
			$sql = "CREATE INDEX idxExchangeRates_date ON exchangeRates (date)";
			$rs = $db->prepare($sql);
			$rs->execute();
		}
	}
?>
