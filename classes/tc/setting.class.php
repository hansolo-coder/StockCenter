<?php
	/**
	 * data management for the settings table
	 */
	class setting
	{
		public $settingName;
		public $settingValue;
		public $settingDesc;
		public $userId;

		
		
		function __construct()
		{
			$this->settingName = '';
			$this->settingValue = '';
			$this->settingDesc = '';

			if(isset($_SESSION['userId']) and $_SESSION['userId'] != '')
			{
				$this->userId = $_SESSION['userId'];
			}
			elseif(isset($_REQUEST['userId']) and $_REQUEST['userId'] != '')
			{
				$this->userId = $_REQUEST['userId'];
			}
		}

		
		/**
		 * selects a record, requires setting name
		 */
		public function select()
		{
			if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbConnect: settings.class.php " . __LINE__ . "</span><br>";}			include_once './classes/db.class.php';

			include_once './classes/db.class.php';
			
			$conn = new db();
			$conn->fileName = $this->userId;
			$db = $conn->connect();

			$sql = "SELECT * FROM settings WHERE settingName=:settingName";
			$rs = $db->prepare($sql);
			$rs->bindValue(':settingName', $this->settingName);
			$rs->execute();
			$row = $rs->fetch();

			$this->settingValue = $row['settingValue'];
			$this->settingDesc = $row['settingDesc'];

		    if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbDisconnect: settings.class.php " . __LINE__ . "</span><br>";}
		    $row  = NULL;
			$rs   = NULL;
			$sql  = NULL;
			$db   = NULL;
			$conn = NULL;
		}

		
		/**
		 * inserts a new setting
		 */
		public function insert()
		{
			if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbConnect: settings.class.php " . __LINE__ . "</span><br>";}			include_once './classes/db.class.php';
			
			include_once './classes/db.class.php';

			$conn = new db();
			$conn->fileName = $this->userId;
			$db = $conn->connect();

			$sql = "SELECT count(*) as theCount FROM settings WHERE settingName=:settingName";
			$rs = $db->prepare($sql);
			$rs->bindValue(':settingName', $this->settingName);
			$rs->execute();
			$row = $rs->fetch();

			if($row['theCount'] == 0)
			{
				$sql = "INSERT INTO settings (settingName, settingValue, settingDesc) VALUES(:settingName, :settingValue, :settingDesc)";
				$rs = $db->prepare($sql);
				$rs->bindValue(':settingName', $this->settingName);
				$rs->bindValue(':settingValue', $this->settingValue);
				$rs->bindValue(':settingDesc', $this->settingDesc);
				$rs->execute();
			}
			else
			{
				$this->errors = "Record Already Exists, Cannot Insert Record";
			}

			if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbDisconnect: settings.class.php " . __LINE__ . "</span><br>";}
			
			$row  = NULL;
			$rs   = NULL;
			$sql  = NULL;
			$db   = NULL;
			$conn = NULL;
		}

		
		/**
		 * updates a setting's value
		 */
		public function update()
		{
			if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbConnect: settings.class.php " . __LINE__ . "</span><br>";}			include_once './classes/db.class.php';
			
			include_once './classes/db.class.php';

			$conn = new db();
			$conn->fileName = $this->userId;
			$db = $conn->connect();

			$sql = "SELECT count(*) as theCount FROM settings WHERE settingName=:settingName";
			$rs = $db->prepare($sql);
			$rs->bindValue(':settingName', $this->settingName);
			$rs->execute();
			$row = $rs->fetch();

			if($row['theCount'] > 0)
			{
				$sql = "UPDATE settings SET settingValue=:settingValue, settingDesc=:settingDesc WHERE settingName=:settingName";
				$rs = $db->prepare($sql);
				$rs->bindValue(':settingName', $this->settingName);
				$rs->bindValue(':settingValue', $this->settingValue);
				$rs->bindValue(':settingDesc', $this->settingDesc);
				$rs->execute();
			}
			else
			{
				$this->errors = "No Record To Update";
			}

			if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbDisconnect: settings.class.php " . __LINE__ . "</span><br>";}
	
			$row  = NULL;
			$rs   = NULL;
			$sql  = NULL;
			$db   = NULL;
			$conn = NULL;
		}

		
		/**
		 * deletes a setting
		 */
		public function delete()
		{
			if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbConnect: settings.class.php " . __LINE__ . "</span><br>";}			include_once './classes/db.class.php';
			
			include_once './classes/db.class.php';

			$conn = new db();
			$conn->fileName = $this->userId;
			$db = $conn->connect();

			$sql = "DELETE FROM settings WHERE settingName=:settingName";
			$rs = $db->prepare($sql);
			$rs->bindValue(':settingName', $this->settingName);
			$rs->execute();

			if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbDisconnect: settings.class.php " . __LINE__ . "</span><br>";}
	
			$rs   = NULL;
			$sql  = NULL;
			$db   = NULL;
			$conn = NULL;
		}
	}
?>