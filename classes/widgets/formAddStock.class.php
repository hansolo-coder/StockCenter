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
			print "<form id='formAddStock' method='post' action='" . htmlentities($_SERVER['PHP_SELF']) . "'>\n";
			print "Symbol or Symbol/ISIN/Name\n";
			print "    <input type='text' id='formAddStockSymbol' name='symbol'>\n";
			print "    <input type='submit' value='Add'>\n";
			print "    <input type='hidden' name='action' value='" . $this->action . "'>\n";
			print "</form>\n";
		}

		
		/**
		 * validates the form
		 */
		function check($symbol)
		{
			# symbol
			if(isset($symbol))
			{
				// Only validate for US region - might not even make sense there (if they buy stocks on foreign exchanges)
				if (isset($_SESSION['region']) and $_SESSION['region'] == 'US') {
					# if symbol is not alpha/numeric only...
					if(!ctype_alnum(trim($symbol)))
					{
						# invalid characters error
						$this->errors .= "Invalid characters in stock symbol<br>";
					}
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
			$symbol = NULL;
			$ISIN = NULL;
			$name = NULL;

			$arr = explode("/", $_REQUEST['symbol']);
			$arr2 = explode("\\", $_REQUEST['symbol']);
			if (count($arr) == 3) {
				$symbol = trim($arr[0]);
				$ISIN = trim($arr[1]);
				$name = trim($arr[2]);
			} elseif (count($arr2) == 3) {
				$symbol = trim($arr2[0]);
				$ISIN = trim($arr2[1]);
				$name = trim($arr2[2]);
                        } else {
				$symbol = $_REQUEST['symbol'];
			}
			$this->check($symbol);
			
			# if there are no errors...
			if($this->errors == "")
			{
				include_once './classes/tc/stocks.class.php';
				
				# add the stock symbol
				$stock = new stocks();
				$stock->symbol = $symbol;
				$stock->ISIN = $ISIN;
				$stock->name = $name;
				$stock->insert();
				
				# get data for symbol from yahoo
				getStaticData($symbol);
				getData($symbol);
				
				# display a success message and the home page
				message("success", "Stock symbol (" . trim(strtoupper($symbol)) . ") added");
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
