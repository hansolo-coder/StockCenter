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
		function display($forYear)
		{
			if ($_SESSION['debug'] == "on"){print "<span class='debug'>listTax($this->action)</span><br>\n";}

			include_once './classes/db.class.php';
    
			$conn = new db();
			$conn->fileName = $_SESSION['userId'];
			$db=$conn->connect();

		        $otherYear = date('Y');
        		if ($forYear == $otherYear)
		        {
		          $otherYear = $otherYear - 1;
		        }

			// get accounts
			$sql = "SELECT accountId, accountNumber, accountName, financialInstitution, isPension, accountType, accountCurrency FROM accounts ORDER BY isPension DESC, aCreated DESC";
			$rs = $db->prepare($sql);
			$rs->execute();
			$accounts = $rs->fetchAll();

			print "<h1>Tax report (" . $forYear . ")</h1>";
			print " (See <a href='" . htmlentities($_SERVER['PHP_SELF']) . "?action=" . $this->action . "&year=" . $otherYear . "'>" . $otherYear . "</a>)";
			print "\n<pre>\n";
			print "<h2>DIVIDENDs per account\n";
			print "=====================</h2>\n";
			$totalCostAllOAccount = 0;
			$totalTaxAllOAccount = 0;
			$totalCostAllPAccount = 0;
			$totalTaxAllPAccount = 0;
			foreach($accounts as $row) {
			  // get dividends for this account
			  $sql = "SELECT t.tDate, t.tDateIsApprox, t.symbol, t.cost, CASE WHEN tax <> '' THEN tax ELSE 0 END AS tax, coalesce(t.currency, a.accountCurrency) AS currency, t.exchangeRate, a.accountCurrency AS ccurrency  FROM transactions t LEFT OUTER JOIN accounts a ON t.accountId = a.accountId WHERE t.activity = 'DIVIDEND' AND t.accountId = :accountId AND t.tDate BETWEEN '" . $forYear . "-01-01' AND '" . $forYear . "-12-31' ORDER BY t.symbol, t.tDate";
			  $rs = $db->prepare($sql);
			  $rs->bindValue(':accountId', $row['accountId']);
			  $rs->execute();
			  $dividends = $rs->fetchAll();
			  if (count($dividends) > 0) {
			    $this->displayAccountInfo($row);
			    $maxLengthSymbol = $this->getMaxLength($dividends, 'symbol', 'Symbol');
			    $maxLengthCost = $this->getMaxLengthMoney($dividends, 'cost', 'currency', 'exchangeRate', 'ccurrency', 'Gross');
			    $maxLengthTax = $this->getMaxLengthMoney($dividends, 'tax', 'currency', 'exchangeRate', 'ccurrency', 'Tax');
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
			    print "\n<u>Account total    : " . $this->formatCashWCurrLocal($totalCost, $trow['ccurrency']) . "</u>\n";
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

			print "<h2>PURCHASEs in year\n";
			print "================</h2>\n";
			$totalCostAllOAccount = 0;
			$totalFeeAllOAccount = 0;
			$totalCostAllPAccount = 0;
			$totalFeeAllPAccount = 0;
			foreach($accounts as $row) {
			  // get purchased shares for this account
			  $sql = "SELECT t1.tDate, t1.tDateIsApprox, t1.symbol, t1.shares, t1.shares * t1.cost AS cost, CASE WHEN t1.tax <> '' THEN t1.tax ELSE 0 END AS tax, COALESCE(t1.currency, a.accountCurrency) AS currency, t1.exchangeRate, a.accountCurrency AS ccurrency, t2.cost AS costFEE, t2.tax AS taxFEE, t2.currency AS currencyFEE, t2.exchangeRate AS exchangeRateFEE FROM transactions t1 LEFT OUTER JOIN accounts a ON t1.accountId = a.accountId LEFT OUTER JOIN transactions t2 ON t1.symbol = t2.symbol AND t1.tDate = t2.tDate AND t1.accountId = t2.accountId AND t2.activity = 'FEE' WHERE t1.activity in ('BUY') AND t1.accountId = :accountId AND t1.tDate BETWEEN '" . $forYear . "-01-01' AND '" . $forYear . "-12-31' ORDER BY t1.symbol, t1.tDate";
			  $rs = $db->prepare($sql);
			  $rs->bindValue(':accountId', $row['accountId']);
			  $rs->execute();
			  $purchases = $rs->fetchAll();
			  if (count($purchases) > 0) {
			    $this->displayAccountInfo($row);
			    $maxLengthSymbol = $this->getMaxLength($purchases, 'symbol', 'Symbol');
			    $maxLengthShares = $this->getMaxLength($purchases, 'shares', 'Shares');
			    $maxLengthCost = $this->getMaxLengthMoney($purchases, 'cost', 'currency', 'exchangeRate', 'ccurrency', 'Cost');
			    $maxLengthFee = $this->getMaxLengthMoney($purchases, 'costFEE', 'currencyFEE', 'exchangeRateFEE', 'ccurrency', 'Fee');
			    $totalCost = 0;
			    $totalFee = 0;

			    print "<u>" . str_pad("Date",10) . " " . str_pad('Symbol', $maxLengthSymbol) . " " . str_pad('Shares', $maxLengthShares) . " " . str_pad('Cost', $maxLengthCost, " ", STR_PAD_LEFT) . " " . str_pad('Fee', $maxLengthFee, " ", STR_PAD_LEFT) . "</u>\n";
			    foreach($purchases as $trow) {
			      // TODO format tDate to region - probably requires storing dates as integers globally (which is probably a good idea anyway)
			      if (isset($_SESSION['region']) and $_SESSION['region'] == 'US') {
			      //  print date("m-d-Y", $trow['tDate']);
			        print $trow['tDate'];
			      } else {
			      //  print date("Y-m-d", $trow['tDate']);
			        print $trow['tDate'];
			      }
			      print " " . str_pad($trow['symbol'], $maxLengthSymbol, " ");
			      print " " . str_pad($trow['shares'], $maxLengthShares, " ");
			      print " " . str_pad($this->formatCashWRateLocal($trow['cost'], $trow['currency'], $trow['exchangeRate'], $trow['ccurrency']), $maxLengthCost, " ", STR_PAD_LEFT);
			      print " " . str_pad($this->formatCashWRateLocal($trow['costFEE'], $trow['currencyFEE'], $trow['exchangeRateFEE'], $trow['ccurrency']), $maxLengthFee, " ", STR_PAD_LEFT);
			      print "\n";
			      $totalCost += $trow['cost'] * $trow['exchangeRate'];
			      $totalFee += $trow['costFEE'] * $trow['exchangeRateFEE'];
			    }
			    $totalCost = toCash($totalCost);
			    $totalFee = toCash($totalFee);
			    if ($row['isPension'] == 'Y') {
			      $totalCostAllPAccount += $totalCost;
			      $totalFeeAllPAccount += $totalFee;
			    } else {
			      $totalCostAllOAccount += $totalCost;
			      $totalFeeAllOAccount += $totalFee;
			    }
			    print "\n<u>Account total    : " . $this->formatCashWCurrLocal($totalCost, $trow['ccurrency']) . "</u>\n";
			    print "<u>Account fee total: " . $this->formatCashWCurrLocal($totalFee, $trow['ccurrency']) . "</u>\n";
			    print "\n";
			  }
			}
			print "<h3>All Ordinary Account total    : " . $this->formatCashWCurrLocal($totalCostAllOAccount, $trow['ccurrency']) . "\n";
			print "All Ordinary Account fee total: " . $this->formatCashWCurrLocal($totalFeeAllOAccount, $trow['ccurrency']) . "\n";
			print "All Pension  Account total    : " . $this->formatCashWCurrLocal($totalCostAllPAccount, $trow['ccurrency']) . "\n";
			print "All Pension  Account fee total: " . $this->formatCashWCurrLocal($totalFeeAllPAccount, $trow['ccurrency']) . "\n";
			print "</h3>\n";

			print "\n";
			print "<h2>SALEs in year\n";
			print "=============</h2>\n";
			print "(The purchase price for these stocks must be looked up manually)\n\n";
			$totalCostAllOAccount = 0;
			$totalFeeAllOAccount = 0;
			$totalCostAllPAccount = 0;
			$totalFeeAllPAccount = 0;
			foreach($accounts as $row) {
			  // get sold shares for this account
			  $sql = "SELECT t1.tDate, t1.tDateIsApprox, t1.symbol, t1.shares, t1.shares * t1.cost AS cost, CASE WHEN t1.tax <> '' THEN t1.tax ELSE 0 END AS tax, COALESCE(t1.currency, a.accountCurrency) AS currency, t1.exchangeRate, a.accountCurrency AS ccurrency, t2.cost AS costFEE, t2.tax AS taxFEE, t2.currency AS currencyFEE, t2.exchangeRate AS exchangeRateFEE FROM transactions t1 LEFT OUTER JOIN accounts a ON t1.accountId = a.accountId LEFT OUTER JOIN transactions t2 ON t1.symbol = t2.symbol AND t1.tDate = t2.tDate AND t1.accountId = t2.accountId AND t2.activity = 'FEE' WHERE t1.activity in ('SELL') AND t1.accountId = :accountId AND t1.tDate BETWEEN '" . $forYear . "-01-01' AND '" . $forYear . "-12-31' ORDER BY t1.symbol, t1.tDate";
			  $rs = $db->prepare($sql);
			  $rs->bindValue(':accountId', $row['accountId']);
			  $rs->execute();
			  $sells = $rs->fetchAll();
			  if (count($sells) > 0) {
			    $this->displayAccountInfo($row);
			    $maxLengthSymbol = $this->getMaxLength($sells, 'symbol', 'Symbol');
			    $maxLengthShares = $this->getMaxLength($sells, 'shares', 'Shares');
			    $maxLengthCost = $this->getMaxLengthMoney($sells, 'cost', 'currency', 'exchangeRate', 'ccurrency', 'Amount');
			    $maxLengthFee = $this->getMaxLengthMoney($sells, 'costFEE', 'currencyFEE', 'exchangeRateFEE', 'ccurrency', 'Fee');
			    $totalCost = 0;
			    $totalFee = 0;

			    print "<u>" . str_pad("Date",10) . " " . str_pad('Symbol', $maxLengthSymbol) . " " . str_pad('Shares', $maxLengthShares) . " " . str_pad('Amount', $maxLengthCost, " ", STR_PAD_LEFT) . " " . str_pad('Fee', $maxLengthFee, " ", STR_PAD_LEFT) . "</u>\n";
			    foreach($sells as $trow) {
			      // TODO format tDate to region - probably requires storing dates as integers globally (which is probably a good idea anyway)
			      if (isset($_SESSION['region']) and $_SESSION['region'] == 'US') {
			      //  print date("m-d-Y", $trow['tDate']);
			        print $trow['tDate'];
			      } else {
			      //  print date("Y-m-d", $trow['tDate']);
			        print $trow['tDate'];
			      }
			      print " " . str_pad($trow['symbol'], $maxLengthSymbol, " ");
			      print " " . str_pad($trow['shares'], $maxLengthShares, " ");
			      print " " . str_pad($this->formatCashWRateLocal($trow['cost'], $trow['currency'], $trow['exchangeRate'], $trow['ccurrency']), $maxLengthCost, " ", STR_PAD_LEFT);
			      print " " . str_pad($this->formatCashWRateLocal($trow['costFEE'], $trow['currencyFEE'], $trow['exchangeRateFEE'], $trow['ccurrency']), $maxLengthFee, " ", STR_PAD_LEFT);
			      print "\n";
			      $totalCost += $trow['cost'] * $trow['exchangeRate'];
			      $totalFee += $trow['costFEE'] * $trow['exchangeRateFEE'];
			    }
			    $totalCost = toCash($totalCost);
			    $totalFee = toCash($totalFee);
			    if ($row['isPension'] == 'Y') {
			      $totalCostAllPAccount += $totalCost;
			      $totalFeeAllPAccount += $totalFee;
			    } else {
			      $totalCostAllOAccount += $totalCost;
			      $totalFeeAllOAccount += $totalFee;
			    }
			    print "\n<u>Account total    : " . $this->formatCashWCurrLocal($totalCost, $trow['ccurrency']) . "</u>\n";
			    print "<u>Account fee total: " . $this->formatCashWCurrLocal($totalFee, $trow['ccurrency']) . "</u>\n";
			    print "\n";
			  }
			}
			print "<h3>All Ordinary Account total    : " . $this->formatCashWCurrLocal($totalCostAllOAccount, $trow['ccurrency']) . "\n";
			print "All Ordinary Account fee total: " . $this->formatCashWCurrLocal($totalFeeAllOAccount, $trow['ccurrency']) . "\n";
			print "All Pension  Account total    : " . $this->formatCashWCurrLocal($totalCostAllPAccount, $trow['ccurrency']) . "\n";
			print "All Pension  Account fee total: " . $this->formatCashWCurrLocal($totalFeeAllPAccount, $trow['ccurrency']) . "\n";
			print "</h3>\n";
/*
select * from transactions where tDate between '2020-01-01' and '2021-12-31' and activity in ('SELL', 'FEE');

select t1.*, t2.cost AS costFEE, t2.tax AS taxFEE, t2.currency AS currencyFEE, t2.exchangeRate AS exchangeRateFEE from transactions t1
left outer join transactions t2 on t1.symbol = t2.symbol and t1.tDate = t2.tDate and t2.activity = 'FEE'
where t1.tDate between '2020-01-01' and '2021-12-31' and t1.activity in ('SELL');
*/
			print "\n";
			print "<h2>Other FEES in year not relatable to other transactions\n";
			print "======================================================</h2>\n";
/*
select * from transactions tf where tf.tDate between '2020-01-01' and '2021-12-31' and tf.activity in ('FEE') AND tf.transactionId NOT IN (
select t2.transactionId from transactions t1
left outer join transactions t2 on t1.symbol = t2.symbol and t1.tDate = t2.tDate and t2.activity = 'FEE'
where t1.tDate between '2020-01-01' and '2021-12-31' and t1.activity in ('SELL','BUY')
);
*/
			$sql = "select tf.tDate, tf.tDateIsApprox, tf.symbol, tf.cost AS cost, CASE WHEN tf.tax <> '' THEN tf.tax ELSE 0 END AS tax, COALESCE(tf.currency, a.accountCurrency) AS currency, tf.exchangeRate, a.accountCurrency AS ccurrency from transactions tf LEFT OUTER JOIN accounts a ON tf.accountId = a.accountId where tf.tDate BETWEEN '" . $forYear . "-01-01' AND '" . $forYear . "-12-31' and tf.activity in ('FEE') AND tf.transactionId NOT IN (select t2.transactionId from transactions t1 left outer join transactions t2 on t1.symbol = t2.symbol and t1.tDate = t2.tDate and t2.activity = 'FEE' where t1.tDate BETWEEN '" . $forYear . "-01-01' AND '" . $forYear . "-12-31' and t1.activity in ('SELL','BUY') )";
			$rs = $db->prepare($sql);
			$rs->execute();
			$nrfees = $rs->fetchAll();
			if (count($nrfees) > 0) {
			  $maxLengthSymbol = $this->getMaxLength($nrfees, 'symbol', 'Symbol');
			  $maxLengthCost = $this->getMaxLengthMoney($nrfees, 'cost', 'currency', 'exchangeRate', 'ccurrency', 'Amount');
			  $totalCost = 0;

			  print "<u>" . str_pad("Date",10) . " " . str_pad('Symbol', $maxLengthSymbol) . " " . str_pad('Amount', $maxLengthCost, " ", STR_PAD_LEFT) . "</u>\n";
			  foreach($nrfees as $trow) {
			    // TODO format tDate to region - probably requires storing dates as integers globally (which is probably a good idea anyway)
			    if (isset($_SESSION['region']) and $_SESSION['region'] == 'US') {
			    //  print date("m-d-Y", $trow['tDate']);
			      print $trow['tDate'];
			    } else {
			    //  print date("Y-m-d", $trow['tDate']);
			      print $trow['tDate'];
			    }
			    print " " . str_pad($trow['symbol'], $maxLengthSymbol, " ");
			    print " " . str_pad($this->formatCashWRateLocal($trow['cost'], $trow['currency'], $trow['exchangeRate'], $trow['ccurrency']), $maxLengthCost, " ", STR_PAD_LEFT);
			  }
			}


			print "</pre>\n";
		}

		function getMaxLength($rows, $column, $heading) {
			$maxLength = 0;
			foreach($rows as $trow) {
				$thisLength = strlen($trow[$column]);
				if ($thisLength > $maxLength)
					$maxLength = $thisLength;
			}
			if (strlen($heading) > $maxLength)
			  $maxLength = strlen($heading);
			return $maxLength;
		}

		function getMaxLengthMoney($rows, $columnMoney, $currency, $rateColumn, $rateCurrency, $heading) {
			$maxLength = 0;
			foreach($rows as $trow) {
				$line = $this->formatCashWRateLocal($trow[$columnMoney], $trow[$currency], $trow[$rateColumn], $trow[$rateCurrency]);
				$thisLength = mb_strlen($line);
				if ($thisLength > $maxLength)
					$maxLength = $thisLength;
			}
			if (strlen($heading) > $maxLength)
			  $maxLength = strlen($heading);
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

		function displayAccountInfo($accountRow) {
			if ($accountRow['isPension'] == 'Y')
			  print "<b>Pension account";
			else
			  print "<b>Ordinary account";
			if (strlen($accountRow['accountType']) > 0)
			  print " (" . $accountRow['accountType'] . ")";
			print "</b>\n<b>" . $accountRow['accountNumber'] . " (" . $accountRow['accountName'] . ") - " . $accountRow['financialInstitution'];
			print "</b>\n";
		}

	}
?>
