<?php
	/**
	 * form for adding a stock
	 */
	class formAddStock
	{
		/**
		 * action field value used in the form
		 * @var string
		 */
		public $action;

		/**
		 * holds any errors from check function
		 * @var string
		 */
		public $errors;
		
		
		
		function __construct()
		{
			$this->action = '';
			$this->errors = '';
		}
		
		
		/**
		 * displays the form
		 */
		function display()
		{
			if ($_SESSION['debug'] == "on"){print "<span class='debug'>addStockForm($this->action)</span><br>\n";}

			print "<h3>Add Stock</h3>\n";
			print "<form method='post' action='" . htmlentities($_SERVER['PHP_SELF']) . "'>\n";
			print "Symbol \n";
			print "    <input type='text' name='symbol'>\n";
			print "    <input type='submit' value='Add'>\n";
			print "    <input type='hidden' name='action' value='" . $this->action . "'>\n";
			print "</form>\n";
		}

		
		/**
		 * validates the form
		 */
		function check()
		{
			# symbol
			if(isset($_REQUEST['symbol']))
			{
				# if symbol is not alpha/numeric only...
				if(!ctype_alnum(trim($_REQUEST['symbol'])))
				{
					# invalid characters error
					$this->errors .= "Invalid characters in stock symbol<br>";
				}
			}
			else 
			{
				# missing symbol error
				$this->errors .= "Stock symbol required<br>";
			}
		}

		
		/**
		 * processes the form
		 */
		function process()
		{
			$this->check();
			
			# if there are no errors...
			if($this->errors == "")
			{
				include_once './classes/tc/stocks.class.php';
				
				# add the stock symbol
				$stock = new stocks();
				$stock->symbol = $_REQUEST['symbol'];
				$stock->insert();
				
				# get data for symbol from yahoo
				getData($_REQUEST['symbol']);
				
				# display a success message and the home page
				message("success", "Stock symbol (" . trim(strtoupper($_REQUEST['symbol'])) . ") added");
				homePage();
			}
			else
			{
				# display errors and the home page
				message("error", $this->errors);
			}
		}
	}
?>