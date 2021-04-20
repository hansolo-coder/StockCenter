<?php
// https://www.sitepoint.com/everything-need-know-html-pre-element/
	/**
	 * form showing taxing information for previous year
	 */
	class listTax
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
			if ($_SESSION['debug'] == "on"){print "<span class='debug'>listTax($this->action)</span><br>\n";}

			include_once './classes/db.class.php';
    
			$conn = new db();
			$conn->fileName = $_SESSION['userId'];
			$db=$conn->connect();

			// get accounts
			$sql = "SELECT accountId, accountNumber, accountName, financialInstitution, isPension, accountType, accountCurrency FROM accounts ORDER BY isPension DESC, aCreated DESC";
			$rs = $db->prepare($sql);
			$rs->execute();
			$accounts = $rs->fetchAll();

			$forYear = date('Y') - 1;
			print "<h1>Tax report (" . $forYear . ")</h1>\n";
			print "<pre>\n";
			print "<h2>DIVIDENDS per account\n";
			print "=====================</h2>\n";
			$totalCostAllOAccount = 0;
			$totalTaxAllOAccount = 0;
			$totalCostAllPAccount = 0;
			$totalTaxAllPAccount = 0;
			foreach($accounts as $row) {
			  // get dividends for this account
			  $sql = "SELECT t.tDate, t.tDateIsApprox, t.symbol, t.cost, t.tax, coalesce(t.currency, a.accountCurrency) AS currency, t.exchangeRate, a.accountCurrency AS ccurrency  FROM transactions t LEFT OUTER JOIN accounts a ON t.accountId = a.accountId WHERE t.activity = 'DIVIDEND' AND t.accountId = :accountId AND t.tDate BETWEEN '" . $forYear . "-01-01' AND '" . $forYear . "-12-31' ORDER BY t.symbol, t.tDate";
			  $rs = $db->prepare($sql);
			  $rs->bindValue(':accountId', $row['accountId']);
			  $rs->execute();
			  $dividends = $rs->fetchAll();
			  if (count($dividends) > 0) {
			    if ($row['isPension'] == 'Y')
			      print "<b>Pension account";
			    else
			      print "<b>Ordinary account";
			    if (strlen($row['accountType']) > 0)
			      print " (" . $row['accountType'] . ")";
			    print "</b>\n<b>" . $row['accountNumber'] . " (" . $row['accountName'] . ") - " . $row['financialInstitution'];
			    print "</b>\n";
			    $maxLengthSymbol = $this->getMaxLength($dividends, 'symbol');
			    $maxLengthCost = $this->getMaxLengthMoney($dividends, 'cost', 'currency', 'exchangeRate', 'ccurrency');
			    $maxLengthTax = $this->getMaxLengthMoney($dividends, 'tax', 'currency', 'exchangeRate', 'ccurrency');
			    $totalCost = 0;
			    $totalTax = 0;
			    print "<u>" . str_pad("Date",10) . " " . str_pad('Symbol', $maxLengthSymbol) . " " . str_pad('Gross', $maxLengthCost, " ", STR_PAD_LEFT);
			    if ($_SESSION['showTransactionTax'] == 'YES')
			      print " " . str_pad('Tax', $maxLengthTax, " ", STR_PAD_LEFT);
			    print "</u>\n";
			    foreach($dividends as $trow) {
			      // TODO format tDate to region - probably requires storing dates as integers globally (which is probably a good idea anyway)
			      if (isset($_SESSION['region']) and $_SESSION['region'] == 'US') {
			      //  print date("m-d-Y", $trow['tDate']);
			        print $trow['tDate'];
			      } else {
			      //  print date("Y-m-d", $trow['tDate']);
			        print $trow['tDate'];
			      }
			      //print " " . str_replace(" ", "&nbsp;", str_pad($trow['symbol'], $maxLengthSymbol, " "));
			      //print " " . str_replace(" ", "&nbsp;", str_pad($this->formatCashWRateLocal($trow['cost'], $trow['currency'], $trow['exchangeRate'], $trow['ccurrency']), $maxLengthCost, " ", STR_PAD_LEFT));
			      print " " . str_pad($trow['symbol'], $maxLengthSymbol, " ");
			      print " " . str_pad($this->formatCashWRateLocal($trow['cost'], $trow['currency'], $trow['exchangeRate'], $trow['ccurrency']), $maxLengthCost, " ", STR_PAD_LEFT);
			      if ($_SESSION['showTransactionTax'] == 'YES')
			        print " " . str_pad($this->formatCashWRateLocal($trow['tax'], $trow['currency'], $trow['exchangeRate'], $trow['ccurrency']), $maxLengthTax, " ", STR_PAD_LEFT);
			      print "\n";
			      $totalCost += $trow['cost'] * $trow['exchangeRate'];
			      $totalTax += $trow['tax'] * $trow['exchangeRate'];
			    }
			    $totalCost = toCash($totalCost);
			    $totalTax = toCash($totalTax);
			    if ($row['isPension'] == 'Y') {
			      $totalCostAllPAccount += $totalCost;
			      $totalTaxAllPAccount += $totalTax;
			    } else {
			      $totalCostAllOAccount += $totalCost;
			      $totalTaxAllOAccount += $totalTax;
			    }
			    print "<u>Account total    : " . $this->formatCashWCurrLocal($totalCost, $trow['ccurrency']) . "</u>\n";
			    if ($_SESSION['showTransactionTax'] == 'YES')
			      print "<u>Account tax total: " . $this->formatCashWCurrLocal($totalTax, $trow['ccurrency']) . "</u>\n";
			    print "\n";
			  }
			}
			print "<h3>All Ordinary Account total    : " . $this->formatCashWCurrLocal($totalCostAllOAccount, $trow['ccurrency']) . "\n";
			if ($_SESSION['showTransactionTax'] == 'YES')
			  print "All Ordinary Account tax total: " . $this->formatCashWCurrLocal($totalTaxAllOAccount, $trow['ccurrency']) . "\n";
			print "All Pension  Account total    : " . $this->formatCashWCurrLocal($totalCostAllPAccount, $trow['ccurrency']) . "\n";
			if ($_SESSION['showTransactionTax'] == 'YES')
			  print "All Pension  Account tax total: " . $this->formatCashWCurrLocal($totalTaxAllPAccount, $trow['ccurrency']) . "\n";
			print "</h3>\n";
			print "<h2>PURCHASE in year\n";
			print "================</h2>\n";
			print "\n";
			print "<h2>SALE in year\n";
			print "=============</h2>\n";
/*
select * from transactions where tDate between '2020-01-01' and '2021-12-31' and activity in ('SELL', 'FEE');

select t1.*, t2.cost AS costFEE, t2.tax AS taxFEE, t2.currency AS currencyFEE, t2.exchangeRate AS exchangeRateFEE from transactions t1
left outer join transactions t2 on t1.symbol = t2.symbol and t1.tDate = t2.tDate and t2.activity = 'FEE'
where t1.tDate between '2020-01-01' and '2021-12-31' and t1.activity in ('SELL');
*/
			print "\n";
			print "<h2>Loose FEES in year\n";
			print "=============</h2>\n";
/*
select * from transactions tf where tf.tDate between '2020-01-01' and '2021-12-31' and tf.activity in ('FEE') AND tf.transactionId NOT IN (
select t2.transactionId from transactions t1
left outer join transactions t2 on t1.symbol = t2.symbol and t1.tDate = t2.tDate and t2.activity = 'FEE'
where t1.tDate between '2020-01-01' and '2021-12-31' and t1.activity in ('SELL','BUY')
);
*/

			print "</pre>\n";
		}

		
		/**
		 * validates the form
		 */
		function check($symbol)
		{
			# symbol
			if(isset($symbol))
			{
				// TODO Perhaps makes this optional by choosing between US and Europe setup
				# if symbol is not alpha/numeric only...
				#if(!ctype_alnum(trim($symbol)))
				#{
				#	# invalid characters error
				#	$this->errors .= "Invalid characters in stock symbol<br>";
				#}
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

		function getMaxLength($dividends, $column) {
			$maxLength = 0;
			foreach($dividends as $trow) {
				$thisLength = strlen($trow[$column]);
				if ($thisLength > $maxLength)
					$maxLength = $thisLength;
			}
			return $maxLength;
		}
		function getMaxLengthMoney($dividends, $columnMoney, $currency, $rateColumn, $rateCurrency) {
			$maxLength = 0;
			foreach($dividends as $trow) {
				$line = $this->formatCashWRateLocal($trow[$columnMoney], $trow[$currency], $trow[$rateColumn], $trow[$rateCurrency]);
				$thisLength = mb_strlen($line);
				if ($thisLength > $maxLength)
					$maxLength = $thisLength;
			}
			return $maxLength;
		}

		function formatCashWRateLocal($cash, $cashCurrency, $rate, $rateCurrency) {
			$line = $this->formatCashWCurrLocal($cash, $cashCurrency);
			if ($rate <> 1) {
				$localAmount = $cash * $rate;
				$line .= " (" . $this->formatCashWCurrLocal($localAmount, $rateCurrency) . ")";
			}
			return $line;
		}

		# formats a cash value including currency
		function formatCashWCurrLocal($value, $currency)
		{
	        # if ($_SESSION['debug'] == "on"){print "<span class='debug'>formatCashWCurr($value)</span><br>\n";}
			if (strlen($currency) > 1)
			  return number_format((float)$value, 2, $_SESSION['DecimalPoint'], $_SESSION['ThousandSep']) . " " . $currency;
			else
			  return $currency . " " . number_format((float)$value, 2, $_SESSION['DecimalPoint'], $_SESSION['ThousandSep']);
		}

	}
?>
