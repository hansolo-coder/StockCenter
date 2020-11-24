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
                // get transactions for the stock
                $sql = "SELECT * FROM transactions WHERE symbol=:symbol ORDER BY tDate DESC";
                $rs = $db->prepare($sql);
                $rs->bindValue(':symbol', $this->symbol);
                $rs->execute();
                $rows = $rs->fetchAll();
    
                if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbDisconnect: transaction.class.php " . __LINE__ . "</span><br>";}
                
                $rs = NULL;
                $db = NULL;
                $conn = NULL;
    
                include_once './classes/pageHeader.class.php';
                $header = new pageHeader();
                $header->display();
                
                summaryBar($_REQUEST['symbol']);
    
                print "<div class='spacer'></div>\n";
    
                dividendEarningsChart($_REQUEST['symbol']);
    
                print "<div class='spacer'></div>\n";
    
                print "<fieldset>\n";
                print "    <legend>\n";
                print "        Activity ($this->symbol)\n";
                print "    </legend>\n";
                print "<table class='data'>\n";
                print "    <tr>\n";
                print "        <th class='data'>\n";
                print "            Date\n";
                print "        </th>\n";
                print "        <th class='data'>\n";
                print "            Activity\n";
                print "        </th>\n";
                print "        <th class='data'>\n";
                print "            Shares\n";
                print "        </th>\n";
                print "        <th class='data'>\n";
                print "            Cost\n";
                print "        </th>\n";
                print "        <th class='data'>\n";
                print "            Total\n";
                print "        </th>\n";
                print "        <th class='data'>\n";
                print "            &nbsp;\n";
                print "        </th>\n";
                print "    </tr>\n";
                print "    <form action='" . htmlentities($_SERVER['PHP_SELF']) . "?action=addTransaction&symbol=" . $this->symbol . "' method='post'>\n";
                print "    <tr>\n";
                print "        <td class='data'>\n";
                print "            <input type='text' name='date' id='date'>\n";
                print "            <script>\n";
                print "                $(function(){\n";
                print "                    var opts = {\n";
                print "                        dateFormat:'yy-mm-dd'\n";
                print "                    };\n";
                print "                    $( '#date' ).datepicker(opts);\n";
                print "                });\n";
                print "            </script>\n";
                print "        </td>\n";
                print "        <td class='data'>\n";
                print "            <select name='activity'>\n";
                print "                <option value='BUY'>BUY</option>\n";
                print "                <option value='SELL'>SELL</option>\n";
                print "                <option value='DIVIDEND'>DIVIDEND</option>\n";
                print "                <option value='FEE'>FEE</option>\n";
                print "            </select>\n";
                print "        </td>\n";
                print "        <td class='data'>\n";
                print "            <input type='text' name='shares'>\n";
                print "        </td>\n";
                print "        <td class='data'>\n";
                print "            $ <input type='text' name='cost'>\n";
                print "        </td>\n";
                print "        <td class='data'>\n";
                print "            -\n";
                print "        </td>";
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
                        $css = "style='background-color: #AFFFAB;'";
                    }
                    elseif($row['activity'] == "FEE")
                    {
                        $css = "style='background-color: #FFB6AB;'";
                    }
    
                    print "    <tr $css>\n";
                    print "        <td class='data'>\n";
                    print "            " . $row['tDate'];
                    print "        </td>\n";
                    print "        <td class='data'>\n";
                    print "            " . $row['activity'];
                    print "        </td>\n";
                    print "        <td class='data'>\n";
                    print "            " . $row['shares'];
                    print "        </td>\n";
                    print "        <td class='data' style='text-align: right;'>\n";
                    print "            $ " . toCash($row['cost']);
                    print "        </td>\n";
                    print "        <td class='data' style='text-align: right;'>\n";
    
                    if($row['activity'] == 'DIVIDEND' OR $row['activity'] == 'FEE')
                    {
                        print "$ " . toCash($row['cost']);
                    }
                    else
                    {
                        print "           $ " . toCash(($row['cost'] * $row['shares']));
                    }
    
                    print "        </td>\n";
                    print "        <td class='data'>\n";
                    print "            <a class='delete' href='index.php?action=deleteTransaction&date=" . $row['tDate'] . "&activity=" . $row['activity'] . "&shares=" . $row['shares'] . "&cost=" . $row['cost'] . "&symbol=" . $row['symbol'] . "'>Delete</a>\n";
                    print "        </td>\n";
                    print "    </tr>\n";
                }
    
                print "</table>\n";
                print "</fieldset>\n";
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
    
            if(isset($_REQUEST['date']) and trim($_REQUEST['date']) != '')
            {
                if(!isset($_REQUEST['shares']) OR trim($_REQUEST['shares']) == '')
                {
                    $shares = 0;
                }
                else
                {
                    $shares = trim($_REQUEST['shares']);
                }
    
                if(isset($_REQUEST['cost']) and trim($_REQUEST['cost']) != '')
                {
                	include_once './classes/tc/transaction.class.php';
    
                	$trans = new transaction();
                	$trans->activity = trim($_REQUEST['activity']);
                	$trans->cost = trim($_REQUEST['cost']);
                	$trans->shares = $shares;
                	$trans->symbol = trim($_REQUEST['symbol']);
                	$trans->tDate = trim($_REQUEST['date']);
    
                	$trans->insert();
    
                    message("success", "Transaction Added");
                    
                    include_once './classes/transactionLog.class.php';
                    
                    $log = new transactionLog();
                    $log->symbol = $_REQUEST['symbol'];
                    $log->showLog();
                }
                else
                {
                    # no cost provided
                    message("error", "No Cost Provided");
                    
                    include_once './classes/transactionLog.class.php';
                    
                    $log = new transactionLog();
                    $log->symbol = $_REQUEST['symbol'];
                    $log->showLog();
                }
            }
            else
            {
                # no date provided
                message("error", "No Date Provided");
    
                include_once './classes/transactionLog.class.php';
                
                $log = new transactionLog();
                $log->symbol = $_REQUEST['symbol'];
                $log->showLog();
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
            $trans->activity = trim($_REQUEST['activity']);
            $trans->cost = trim($_REQUEST['cost']);
            $trans->shares = trim($_REQUEST['shares']);
            $trans->symbol = trim($_REQUEST['symbol']);
            $trans->tDate = trim($_REQUEST['date']);
    
            $trans->delete();
    	}
    }
?>