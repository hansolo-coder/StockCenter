<?php
    /**
     * manages the transaction log
     */
    class listTransactionLog
    {
        /**
         * the stock to show transactions for
         * @var string
         */
        public $symbol;
        
        
        /**
         * display the transaction log
         */
        function showLog()
        {
            if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbConnect: transaction.class.php " . __LINE__ . "</span><br>";}
    
            include_once './classes/db.class.php';
    
            $conn = new db();
            $conn->fileName = $_SESSION['userId'];
            $db=$conn->connect();
    
    		# if we have a symbol...
            if (isset($this->symbol) and trim($this->symbol) != '')
            {
		// Check stock
		include_once './classes/tc/stocks.class.php';
		$stock = new stocks();
		$stock->symbol = $this->symbol;
		$stock->select();

                // get transactions for the stock
                $sql = "SELECT accounts.accountNumber, accounts.accountName, transactions.*, coalesce(transactions.currency, accounts.accountCurrency) AS ccurrency FROM transactions LEFT OUTER JOIN accounts ON transactions.accountId = accounts.accountId WHERE symbol=:symbol ORDER BY tDate DESC";
                $rs = $db->prepare($sql);
                $rs->bindValue(':symbol', $this->symbol);
                $rs->execute();
                $rows = $rs->fetchAll();
                $ccurrency = "";
                if (count($rows) > 0) {
                  $ccurrency = $rows[0]['ccurrency'];
                } else {
                  $ccurrency = $_SESSION['DefaultCurrency'];
                }

                // get accounts
                $sql = "SELECT accountId, accountNumber, accountName FROM accounts ORDER BY aCreated DESC";
                $rs = $db->prepare($sql);
                $rs->execute();
                $accounts = $rs->fetchAll();

                if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbDisconnect: transaction.class.php " . __LINE__ . "</span><br>";}
                
                $rs = NULL;
    
                summaryBar($_REQUEST['symbol']);
    
                print "<div class='spacer'></div>\n";

		// Show window to manually update current value of stock
		if ($stock->skipLookup == 1) {
		  include_once './classes/tc/stockData.class.php';
		  $stockdata = new stockData();
		  $stockdata->symbol = $this->symbol;
		  $stockdata->select();

                  print "<fieldset>\n";
                  print "    <legend>\n";
                  print "        Set current price of: ($stock->symbol \ $stock->name)\n";
                  print "    </legend>\n";
                  print "    <table>\n";
                  print "      <tr>\n";
                  print "        <th class='data'>Date</th>\n";
                  print "        <th class='data'>Value</th>\n";
                  print "        <th class='data'>Name</th>\n";
                  print "        <th class='data'>&nbsp;</th>\n";
                  print "      </tr>\n";
		  print "      <form action='" . htmlentities($_SERVER['PHP_SELF']) . "?action=setStockPrice&symbol=" . $this->symbol . "' method='post'>\n";
                  print "      <tr>\n";
                  print "        <td class='data'>";
                  print "            <input type='text' name='date' id='pricedate' value='";
                  if (isset($_SESSION['region']) and $_SESSION['region'] == 'US')
		    print date("m-d-Y", $stockdata->lastUpdated) . "'>\n";
		  else
		    print date("Y-m-d", $stockdata->lastUpdated) . "'>\n";
                  print "            <script>\n";
                  print "                $(function(){\n";
                  print "                    var opts = {\n";
                  if (isset($_SESSION['region']) and $_SESSION['region'] == 'US')
                    print "                        dateFormat:'mm-dd-yy'\n";
                  else
                    print "                        dateFormat:'yy-mm-dd'\n";
                  print "                    };\n";
                  print "                    $( '#pricedate' ).datepicker(opts);\n";
                  print "                });\n";
                  print "            </script>\n";
		  print "        </td>\n";
                  print "        <td class='data'>";
                  print "            <input type='text' name='price' id='price' value='";
		  print $stockdata->currentPrice;
		  print "'></td>\n";
                  print "        <td class='data'>";
                  print "            <input type='text' name='name' id='name' value='";
		  print $stockdata->name;
		  print "'></td>\n";
                  print "        <td class='data'>";
		  print "            <input type='submit' value='Save'>\n";
		  print "        </td>\n";
                  print "      </tr>\n";
                  print "      </form>\n";
                  print "    </table>\n";
                  print "</fieldset>\n";
                  print "<div class='spacer'></div>\n";
		}

		include_once './classes/widgets/formCharts.class.php';
		$charts = new formCharts();

		$charts->action = "dividendEarnings";
		$charts->parm1  = $this->symbol;
		$charts->display($db);
    
                print "<div class='spacer'></div>\n";
                print "<a name='transactions'>\n";
    
                print "<fieldset>\n";
                print "    <legend>\n";
                print "        Activity ($this->symbol)\n";
                print "    </legend>\n";
                print "<table class='data'>\n";
                print "   <thead>\n";
                print "    <tr>\n";
                print "        <th class='data'>Account</th>\n";
                print "        <th class='data'>Date</th>\n";
                print "        <th class='data'>Activity</th>\n";
                print "        <th class='data'>Shares</th>\n";
                print "        <th class='data'>Cost</th>\n";
		if ($_SESSION['showTransactionTax'] == 'YES')
		{
	                print "        <th class='data'>Tax</th>\n";
		}
                print "        <th class='data'>Total</th>\n";
                print "        <th class='data'>\n";
                print "            &nbsp;\n";
                print "        </th>\n";
                print "    </tr>\n";
                print "   </thead>\n";
                print "   <tbody>\n";
                print "    <form action='" . htmlentities($_SERVER['PHP_SELF']) . "?action=addTransaction&symbol=" . $this->symbol . "#transactions' method='post'>\n";
                print "    <tr>\n";
                print "        <td class='data'>\n";
                print "            <select name='accountId' class='numbers'>\n";
                foreach($accounts as $row)
                {
                  print "                <option value='" . $row['accountId'] . "'>" . $row['accountName'] . "</option>\n";
		}
                print "            </select>\n";
                print "        </td>\n";
                print "        <td class='data'>\n";
                print "          <span class='nobreakcenterv'>\n";
                print "            <div class='tooltip'>\n";
                print "              <input type='checkbox' name='approxdate' id='approxdate' class='mini'>\n";
                print "              <div class='tooltiptext'>Select this to mark date as approximate</div>\n";
                print "            </div>\n";
                print "            <input type='text' name='date' id='date' required class='date'>\n";
                print "            <script>\n";
                print "                $(function(){\n";
                print "                    var opts = {\n";
                if (isset($_SESSION['region']) and $_SESSION['region'] == 'US')
                  print "                        dateFormat:'mm-dd-yy'\n";
                else
                  print "                        dateFormat:'yy-mm-dd'\n";
                print "                    };\n";
                print "                    $( '#date' ).datepicker(opts);\n";
                print "                });\n";
                print "            </script>\n";
                print "          </span>\n";
                print "        </td>\n";
                print "        <td class='data'>\n";
                print "            <select name='activity' class='activity'>\n";
                print "                <option value='BUY'>BUY</option>\n";
                print "                <option value='SELL'>SELL</option>\n";
                print "                <option value='DIVIDEND'>DIVIDEND</option>\n";
                print "                <option value='FEE'>FEE</option>\n";
                print "                <option value='BONUS'>BONUS</option>\n";
                print "                <option value='SPLIT'>SPLIT</option>\n";
                print "                <option value='MOVE'>MOVE</option>\n";
                print "            </select>\n";
                print "        </td>\n";
                print "        <td class='data'>\n";
                print "            <input type='text' name='shares' maxlength='10' class='date'>\n";
                print "        </td>\n";
                print "        <td class='data'>\n";
// TODO skal nok enten være currency fra stock-tabel, currency fra account, currency fra settings (men ikke currency fra transactions)
                print "            <span class='nobreakcenterv'><label class='currencyamount' for='cost'>" . $ccurrency . "</label><input type='text' id='cost' name='cost' maxlength='11' class='amount' required></span>\n";
                print "        </td>\n";
		if ($_SESSION['showTransactionTax'] == 'YES')
		{
	                print "        <td class='data'>\n";
                	print "            <span class='nobreakcenterv'><label class='currencyamount' for='tax'>" . $ccurrency . "</label><input type='text' id='tax' name='tax' maxlength='11' class='amount'></span>\n";
	                print "        </td>\n";
		}
                print "        <td class='data'>\n";
                print "            -\n";
                print "        </td>\n";
                print "        <td class='data'>\n";
                print "            <input type='submit' value='Save'>\n";
                print "        </td>\n";
                print "    </tr>\n";
                print "    </form>\n";
    
                // set row color based on activity type
                foreach($rows as $row)
                {
                    if($row['activity'] == "BUY")
                    {
                        $css = "style='background-color: #ABD9FF;'";
                    }
                    elseif($row['activity'] == "SELL")
                    {
                        $css = "style='background-color: #AFFFAB;'";
                    }
                    elseif($row['activity'] == "DIVIDEND")
                    {
                        $css = "style='background-color: #AFFF8B;'";
                    }
                    elseif($row['activity'] == "FEE")
                    {
                        $css = "style='background-color: #FFB6AB;'";
                    }
                    elseif($row['activity'] == "BONUS") # Bonus Shares - alternative to split
                    {
                        $css = "style='background-color: #8BD9FF;'";
                    }
                    elseif($row['activity'] == "SPLIT") # Stock Split
                    {
                        $css = "style='background-color: #69D9FF;'";
                    }
    
                    print "    <tr $css>\n";
                    print "        <td class='data'><div class='tooltip'>" . $row['accountName'] . "<div class='tooltiptext'>" . $row['accountNumber'] . "</div></div></td>\n";
		    // TODO format tDate according to region
		    if ($row['tDateIsApprox'] != 0) {
                      print "        <td class='data'>Approx. " . $row['tDate']         . "</td>\n";
		    } else {
                      print "        <td class='data'>" . $row['tDate']         . "</td>\n";
		    }
                    print "        <td class='data'>" . $row['activity']      . "</td>\n";
                    print "        <td class='data'>" . $row['shares']        . "</td>\n";
                    print "        <td class='data' style='text-align: right;'>";
                    print formatCashWCurr($row['cost'], $row['ccurrency']);
                    print "</td>\n";
                    if ($_SESSION['showTransactionTax'] == 'YES')
                    {
                        print "        <td class='data' style='text-align: right;'>";
                        print formatCashWCurr($row['tax'], $row['ccurrency']);
                        print "</td>\n";
                    }
                    print "        <td class='data' style='text-align: right;'>";
    
                    if($row['activity'] == 'DIVIDEND' OR $row['activity'] == 'FEE')
                    {
                        print formatCashWCurr($row['cost'], $row['ccurrency']);
                    }
                    else
                    {
                        print formatCashWCurr(($row['cost'] * $row['shares']), $row['ccurrency']);
                    }
    
                    print         "</td>\n";
                    print "        <td class='data'>\n";
                    print "            <a class='delete' href='index.php?action=deleteTransaction&id=" . $row['transactionId'] . "&symbol=" . $row['symbol'] . "#transactions'>Delete</a>\n";
                    print "        </td>\n";
                    print "    </tr>\n";
                }
    
                print "   </tbody>\n";
                print "</table>\n";
                print "</fieldset>\n";

		print "        <div class='spacer'></div>\n";

		$charts->action = "stockPriceHistory";
		$charts->parm1  = $this->symbol;
		$charts->display($db);

		$charts->printExecuteScripts();

                $db = NULL;
                $conn = NULL;
            }
            else
            {
                homePage();
            }
        }
        
        
        
    	/**
    	 * adds a new transaction to the log
    	 */
    	function addTransaction()
    	{
            if ($_SESSION['debug'] == "on"){print "<span class='debug'>transactionLog-> addTransaction()</span><br>";}
    
            if(isset($_REQUEST['date']) and trim($_REQUEST['date']) == '')
            {
                # no date provided
                message("error", "No Date Provided");
    
                include_once './classes/widgets/listTransactionLog.class.php';
                
                $log = new listTransactionLog();
                $log->symbol = $_REQUEST['symbol'];
                $log->showLog();
            }
            elseif (isset($_REQUEST['accountId']) and trim($_REQUEST['accountId']) == '')
            {
                # no account provided
                message("error", "No Account Provided");
    
                include_once './classes/widgets/listTransactionLog.class.php';
                
                $log = new listTransactionLog();
                $log->symbol = $_REQUEST['symbol'];
                $log->showLog();
            }
            else
            {
                if(!isset($_REQUEST['shares']) OR trim($_REQUEST['shares']) == '')
                {
		    if (isset($_REQUEST['activity']) AND $_REQUEST['activity']=='DIVIDEND')
                       $shares = NULL;
		    else
                       $shares = 0;
                }
                else
                {
                    $shares = trim($_REQUEST['shares']);
                }
    
                if(isset($_REQUEST['cost']) and trim($_REQUEST['cost']) != '')
                {

			include_once './classes/db.class.php';
    
			$conn = new db();
			$conn->fileName = $_SESSION['userId'];
			$db=$conn->connect();
			include_once './classes/tc/stockData.class.php';
			$stock = new stockData();
			$stock->symbol = trim($_REQUEST['symbol']);
			$stock->select();

                	include_once './classes/tc/transaction.class.php';

			// TODO skal nok være stock-currency, account-currency eller defaultcurrency (men ikke transaktionscurrency som pt.)
			$amountAndCurr = parseAmountAndCurr($_REQUEST['cost'], $stock->currency, $_SESSION['DefaultCurrency']);

                	$trans = new transaction();
                	$trans->accountId = trim($_REQUEST['accountId']);
                	$trans->activity = trim($_REQUEST['activity']);
                	$trans->cost = trim($amountAndCurr[0]);
			$trans->currency = trim($amountAndCurr[1]);
                	$trans->shares = $shares;
                	$trans->symbol = trim($_REQUEST['symbol']);
                	$trans->tDate = trim($_REQUEST['date']);
			if (isset($_REQUEST['tax']))
			{
				$trans->tax = trim($_REQUEST['tax']);
			}
			if (isset($_REQUEST['approxdate']) && $_REQUEST['approxdate'] == 'on') {
				$trans->DateIsApprox = 1;
			}
    
                	$trans->insert();
    
                    message("success", "Transaction Added");
                    
                    include_once './classes/widgets/listTransactionLog.class.php';
                    
                    $log = new listTransactionLog();
                    $log->symbol = $_REQUEST['symbol'];
                    $log->showLog();
                }
                else
                {
                    # no cost provided
                    message("error", "No Cost Provided");
                    
                    include_once './classes/widgets/listTransactionLog.class.php';
                    
                    $log = new listTransactionLog();
                    $log->symbol = $_REQUEST['symbol'];
                    $log->showLog();
                }
            }
    	}    
    


    	/**
    	 * removes a transaction from the log
    	 */
    	function deleteTransaction()
    	{
            if ($_SESSION['debug'] == "on"){print "<span class='debug'>deleteTransaction()</span><br>";}
    
            include_once './classes/tc/transaction.class.php';
    
            $trans = new transaction();
            $trans->transactionId = trim($_REQUEST['id']);
            $trans->symbol = trim($_REQUEST['symbol']);
    
            $trans->deleteById();
    	}

	/**
	 * Set a fixed stockprice (because it cannot be looked up automatically
	 */
	function setStockPrice()
	{
            if ($_SESSION['debug'] == "on"){print "<span class='debug'>setStockPrice()</span><br>";}
    
            include_once './classes/tc/stockData.class.php';
    
            $sd = new stockData();
	    $sd->symbol = trim($_REQUEST['symbol']);
	    $sd->deleteCategory('MANUAL');

	    $sd = new stockData();
	    $sd->symbol = trim($_REQUEST['symbol']);
	    $sd->currentPrice = trim($_REQUEST['price']);
	    $sd->name = trim($_REQUEST['name']);
	    $sd->insertSimple('MANUAL');
	}
    }
?>
