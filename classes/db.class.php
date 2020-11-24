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
			$sql .= "[symbol] VARCHAR(10),";
			$sql .= "[market] VARCHAR(10),";
			$sql .= "[attribute] VARCHAR(100),";
			$sql .= "[value] VARCHAR(200),";
			$sql .= "[lastUpdated] INTEGER)";
			$rs = $db->prepare($sql);
			$rs->execute();

			# stocks table
			$sql = "CREATE TABLE [stocks] ([symbol] varchar(5))";
			$rs = $db->prepare($sql);
			$rs->execute();

			# transaction table
			$sql = "CREATE TABLE [transactions] ([tDate] DATE,[symbol] VARCHAR(5),[activity] VARCHAR(10),[shares] INT,[cost] INT(0, 2))";
			$rs = $db->prepare($sql);
			$rs->execute();

			# settings table
			$sql = "CREATE TABLE [settings] ([settingName] VARCHAR(100),settingValue VARCHAR(100),settingDesc VARCHAR(100))";
			$rs = $db->prepare($sql);
			$rs->execute();

			$sql = "INSERT INTO settings (settingName, settingValue, settingDesc) VALUES('sellTrigger', '.25', 'Decimal growth percentage to signal possible sale')";
			$rs = $db->prepare($sql);
			$rs->execute();

			$sql = "INSERT INTO settings (settingName, settingValue) VALUES('password', '" . md5($this->password) . "')";
			$rs = $db->prepare($sql);
			$rs->execute();

			$sql = "INSERT INTO settings (settingName, settingValue, settingDesc) VALUES('refreshTime', '15', 'Time between yahoo data refreshes')";
			$rs = $db->prepare($sql);
			$rs->execute();

			$sql = "INSERT INTO settings (settingName, settingValue, settingDesc) VALUES('databaseVersion', '2', 'Database schema version')";
			$rs = $db->prepare($sql);
			$rs->execute();
		}
	}
?>