<?php
	/**
	 * form for logging in
	 */
	class formLogin
	{
		/**
		 * action value for form
		 * @var string
		 */
		public $action;
		
		/**
		 * errors found during check
		 * @var string
		 */
		public $errors;

		/**
		 * Default username
		 */
		public $username;

		function __construct()
		{
			$this->username = '';
		}
		
		/**
		 * displays the login form
		 */
		function display()
		{
			print "<!DOCTYPE html>\n";
			print "<html>\n";
			print "    <head>\n";
			print "        <title>Stock Center: Login</title>\n";
			print "        <meta charset='UTF-8'>\n";
			// https://stackoverflow.com/questions/356809/best-way-to-center-a-div-on-a-page-vertically-and-horizontally
			print "        <style>\n";
			print "          div.center {\n";
			print "          	width: 260px;\n";
			print "          	height: 260px;\n";
			print "\n";
			print "          	position: absolute;\n";
			print "          	top:0;\n";
			print "          	bottom: 0;\n";
			print "          	left: 0;\n";
			print "          	right: 0;\n";
			print "\n";
			print "          	margin: auto;\n";
			print "          }\n";
			print "        </style>\n";
			print "    </head>\n";
			print "    <body>\n";
//			print "        <div style='height: 25%;'></div>\n";
			print "       <div class='center'>\n";
			print "        <div style='width: 260px; background-color: #E6E6E6; padding: 10px; margin: auto; text-align: center; font-size: 12pt; font-weight: bold;'>\n";
			print "        Stock Center\n";
			print "        </div>\n";
			print "        <div style='width: 260px; background-color: #E6E6E6; padding: 10px; margin: auto;'>\n";
			print "            <form action='" . $_SERVER['PHP_SELF'] . "' method='post' autocomplete='off' onsubmit='return(validate());'>\n";
			print "                User ID<br>\n";
			print "                <input type='text' name='userId' style='width: 90%;'";
			if (strlen($this->username) > 0)
			  print " value='" . strip_tags(htmlspecialchars($this->username)) . "'";
			else
			  print " autofocus";
			print "><br>\n";
			print "                Password<br>\n";
			print "                <input type='password' name='pass' style='width: 90%;'";
			if (strlen($this->username) > 0)
			  print " autofocus";
			print "><br>\n";
			print "                <input type='hidden' name='action' value='" . $this->action . "'>\n";
			print "                <br>\n";
			print "                <div style='text-align: right;'>\n";
			print "                    <input type='submit' value='Login'>\n";
			print "                </div>\n";
			print "            </form>\n";
			print "        </div>\n";

			if ($this->errors != '')
			{
				print "        <div style='color: red; text-align: center;'>\n";
				print "            " . $this->errors . "\n";
				print "        </div>\n";
			}

			print "       </div>\n";

			print "        <div style='height: 30px;'></div>\n";
			print "        <div style='border: 1px dotted; width: 550px; margin: auto; text-align: center; font-family: arial; font-size: 10pt;'>\n";
			print "           <p>\n";
			print "Stock Center is open source and licensed under the <a href='http://opensource.org/licenses/MIT'>MIT License</a>\n";
			print "           </p>\n";
			print "        </div>\n";
			print "    </body>\n";
			print "</html>\n";
		}

		
		/**
		 * validates the form
		 */
		function check()
		{
			# user id
			if (isset($_REQUEST['userId']) and trim($_REQUEST['userId']) != "")
			{
				# if a data file for this login does not exist...
				if (!file_exists("./data/" . trim($_REQUEST['userId']) . ".sqlite"))
				{
					# user not found error
					$this->errors = 'User not found';
				}
			}
			else
			{
				# user id empty error
				$this->errors = 'No user name provided';
			}
			
			# password
			if (isset($_REQUEST['pass']) and trim($_REQUEST['pass']) != "")
			{
				
			}
			else 
			{
				# password empty error
				$this->errors = 'No password provided';
			}
		}

		
		/**
		 * processes the form
		 */
		function process()
		{
			$this->check();
			
			# if there were no errors...
			if($this->errors == '')
			{
				include_once './classes/tc/setting.class.php';
	
				# get the password from the database
				$s = new setting();
				$s->settingName = 'password';
				$s->select();
	
				# if it matches the provided password...
				if ($s->settingValue == md5(trim($_REQUEST['pass'])))
				{
				    # set session variables
				    $_SESSION['userId'] = $_REQUEST['userId'];
				    $_SESSION['loggedIn'] = "y";
	
				    include_once './classes/db.class.php';
					
				    if ($_SESSION['debug'] == "on"){
					print "<span class='debug'>dbConnect: loginForm.class.php " . __LINE__ . "</span><br>";
				    }
	
				    $conn = new db();
				    $conn->fileName = $_SESSION['userId'];
				    $db=$conn->connect();
	
				    # upgrade the database if needed
				    // TODO re-enable automatic database-upgrade at some later time
				    //dbUpgrade($db);

				    # get the settings from the settings table
				    $sqlSettings = "SELECT * FROM settings";
				    $rsSettings = $db->prepare($sqlSettings);
				    $rsSettings->execute();
				    $rows = $rsSettings->fetchAll();
	
				    if ($_SESSION['debug'] == "on"){
					print "<span class='debug'>dbDisconnect: loginForm.class.php " . __LINE__ . "</span><br>";
				    }
	
				    $rsSettings = NULL;
				    $db = NULL;
				    $conn = NULL;
	
				    # set the refresh time from the setting
				    foreach($rows as $rowSettings)
				    {
					if ($rowSettings['settingName'] == "refreshTime")
					{
						$_SESSION['refreshTime'] = $rowSettings['settingValue'];
					}
					if ($rowSettings['settingName'] == "currency")
					{
						$_SESSION['DefaultCurrency'] = $rowSettings['settingValue'];
					}
					if ($rowSettings['settingName'] == "showTransactionTax")
					{
						$_SESSION['showTransactionTax'] = strtoupper(trim($rowSettings['settingValue']));
					}
					if ($rowSettings['settingName'] == "region")
					{
						$_SESSION['region'] = strtoupper(trim($rowSettings['settingValue']));
					}
					if ($rowSettings['settingName'] == "chgPctMarkUnchanged")
					{
						$_SESSION['chgPctMarkUnchanged'] = $rowSettings['settingValue'];
					}
				    }
				    if ($_SESSION['region'] == 'US') {
					$_SESSION['DecimalPoint'] = '.';
					$_SESSION['ThousandSep'] = ',';
				    } else {
					$_SESSION['DecimalPoint'] = ',';
					$_SESSION['ThousandSep'] = '.';
				    }

				    include_once './classes/tc/exchangeRates.class.php';
				    $exchangeRates = new exchangeRates();
				    $_SESSION['Rates'] = $exchangeRates->select($_SESSION['DefaultCurrency']);
	
				    # display the home page
				    include_once './classes/page.class.php';
				    $page = new page();
				    $page->start();

				    if ($exchangeRates->errors != '')
				        message('error', $exchangeRates->errors);

				    homePage();
					
				    $page->end();
				}
				else
				{
					usleep(1400000); // Delay for password-crackers
					# login failed
					$this->action = 'login';
					$this->errors = 'Login Failed';
					$this->display();
				}
			}
		}
	}
?>
