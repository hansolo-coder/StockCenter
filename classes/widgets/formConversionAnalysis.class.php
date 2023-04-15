<?php
    /**
     * form for running a stock conversion analysis
     */
    class formConversionAnalysis
    {
        /**
         * the action value for the form
         * @var string
         */
        public $action;
        
        
        
        /**
         * displays the form
         */
        function display()
        {
        	if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbConnect: " . __LINE__ . "</span><br>";}
        	
        	include_once './classes/db.class.php';
        	
        	# get a list of stocks
        	$conn = new db();
            $conn->fileName = $_SESSION['userId'];
            $db=$conn->connect();
    
    		$sql = "SELECT * FROM stocks ORDER BY symbol";
    		$rs = $db->prepare($sql);
    		$rs->execute();
    
    		# display the form
            print "<div class='spacer'></div>";
    		print "<div style='width: 300px; padding: 10px; margin: auto;'>";
    		print "    <form action='" . $_SERVER['PHP_SELF'] . "' method='post'>";
            print "        <table class='data'>";
            print "            <tr>";
            print "                <th class='data' colspan='2'>";
            print "                    Conversion Analysis";
            print "                </th>";
            print "            </tr>";
            print "            <tr>";
            print "                <td class='data'>";
            print "    	               Select Stock To Convert";
            print "                </td>";
            print "                <td class='data'>";
    		print "                    <select name='symbol' style='width: 130px;'>";
    		print "                        <option selected value=''>Select Symbol</option>";
    
    		while ($row = $rs->fetch())
    		{
    			print "                        <option value='" . $row['symbol'] . "'>" . $row['symbol'] . "</option>";
    		}
    
    		print "                        </select>";
            print "                </td>";
            print "            </tr>";
            print "        </table>";
    		print "        <input type='hidden' name='action' value='" . $this->action . "'>";
            print "        <div style='text-align: right;'>";
    		print "            <input type='submit' value='Analyze' style='width: 100px;'>";
            print "        </div>";
    		print "    </form>";
    		print "</div>";
    		
    		if ($_SESSION['debug'] == "on"){
    			print "<span class='debug'>dbDisconnect: " . __LINE__ . "</span><br>";
    		}
    		
    		$row = null;
    		$rs = null;
    		$db = null;
    		$conn = null;
        }
        
        
        /**
         * validates the form
         */
        function check()
        {
        	
        }
        
        
        /**
         * processes the form
         */
        function process()
        {
            include_once './classes/tc/stockData.class.php';
    
            # get the data for the stock to convert
            $sellData = new stockData();
            $sellData->symbol = $_REQUEST['symbol'];
            $sellData->select();
    
            $sellCurrentPrice = $sellData->currentPrice2;
            $sellCurrentDps = $sellData->dps;
    
            if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbConnect: " . __LINE__ . "</span><br>";}
        	
            include_once './classes/db.class.php';
        	
            $conn = new db();
            $conn->fileName = $_SESSION['userId'];
            $db=$conn->connect();
            
            # get the number of shares bought
            $sql = "SELECT sum(shares) as s FROM transactions where activity IN ('BUY','BONUS','SPLIT') AND symbol=:symbol";
            $rs = $db->prepare($sql);
            $rs->bindValue(':symbol', $_REQUEST['symbol']);
            $rs->execute();
            $row = $rs->fetch();
            $boughtShares = $row['s'];
    
            # get the number of shares sold
            $sql = "SELECT sum(shares) as s FROM transactions where activity='SELL' AND symbol=:symbol";
            $rs = $db->prepare($sql);
            $rs->bindValue(':symbol', $_REQUEST['symbol']);
            $rs->execute();
            $row = $rs->fetch();
            $soldShares = $row['s'];
    
            # calculate owned share count
            $sharesToSell = ($boughtShares - $soldShares);
    
            # calculate total dividends lost by sale
            $soldAnnualDps = toCash(($sharesToSell * $sellCurrentDps));
            $soldQtrDps = toCash(($soldAnnualDps / 4));
    
            # get the current total value of held shares
            $sellValue = ($sellCurrentPrice * $sharesToSell);
    
            # get a list of all your stocks
            $sqlStockList = "SELECT * FROM stocks ORDER BY symbol";
            $rsStockList = $db->prepare($sqlStockList);
            $rsStockList->execute();
            $stockList = $rsStockList->fetchAll();
    
            # begin the data table
            $this->action = 'conversionStep2';
            $this->display();
    
            print "<fieldset>";
            print "    Selling your shares in " . $_REQUEST['symbol'] . " would generate " . formatCash($sellValue) . " to work with.<br>";
            print "    This holding also produced an annual dividend income of " . formatCash($soldAnnualDps) . " or a quarterly income of " . formatCash($soldAnnualDps / 4) . ".<br>";
            print "    Below is a \"what if\" simulation of how your dividend income would change if you re-invested the income from the sale into each of your holdings.";
            print "</fieldset>";
            print "<div class='spacer'></div>";
            print "<fieldset>";
            print "<legend>Conversion Analysis Results</legend>";
            print "<table class='display' id='data'>";
            print "    <thead>";
            print "    <tr>";
            print "        <th class='data'>";
            print "            Symbol";
            print "        </th>";
            print "        <th class='data'>";
            print "            Current Price";
            print "        </th>";
            print "        <th class='data'>";
            print "            Shares Acquired";
            print "        </th>";
            print "        <th class='data'>";
            print "            DPS";
            print "        </th>";
            print "        <th class='data'>";
            print "            Calculation";
            print "        </th>";
            print "        <th class='data'>";
            print "            Quarterly Dividend Change";
            print "        </th>";
            print "        <th class='data'>";
            print "            Annual Dividend Change";
            print "        </th>";
            print "    </tr>";
            print "    </thead>";
            print "    <tbody>";
    
    		if ($_SESSION['debug'] == "on"){
    			print "<span class='debug'>dbDisconnect: " . __LINE__ . "</span><br>";
    		}
    		
    		$row = null;
    		$rs = null;
    		$rsStockList = null;
    		$db = null;
    		$conn = null;
            
            foreach($stockList as $stock)
            {
            	# if the stock isn't the same one we are selling...
                if($stock['symbol'] != $_REQUEST['symbol'])
                {
                	# get the stock's info
                	$buyData = new stockData();
                	$buyData->symbol = $stock['symbol'];
                	$buyData->select();
    
                    $buyCurrentPrice = $buyData->currentPrice2;

                    if ($buyCurrentPrice != 0 && is_numeric($buyData->dps))
                    {
                      # divide money to spend by stock price to get # of shares we can buy
                      $sharesToBuy = toCash(($sellValue / $buyCurrentPrice));
    
                      # calculate current dps for stock being bought
                      $boughtAnnualDps = toCash(($sharesToBuy * $buyData->dps));
                      $boughtQtrDps = toCash(($boughtAnnualDps / 4));
    
                      # calculate total dividends gained
                      $annualDelta = toCash(($boughtAnnualDps - $soldAnnualDps));
                      $qtrDelta = toCash($annualDelta / 4);
    
                      # decide what color to highlight cells
                      if($annualDelta > 0)
                      {
                          $css = "style='background-color: #AFFFAB;'";
                      }
                      else
                      {
                          $css = "style='background-color: #FFB6AB;'";
                      }
                    } else {
                      $sharesToBuy = "Cannot calc. No price/dps";
                      $css = "style='background-color: lightgray;'";
                      $annualDelta = "";
                      $qtrDelta = "";
                    }
                    # display the information
                    print "    <tr>";
                    print "        <td class='data'>";
                    print "            " . $stock['symbol'];
                    print "        </td>";
                    print "        <td class='data'>";
                    print "            " . formatCash($buyCurrentPrice);
                    print "        </td>";
                    print "        <td class='data'>";
                    print "            " . $sharesToBuy;
                    print "        </td>";
                    print "        <td class='data'>";
                    print "            " . $buyData->dps;
                    print "        </td>";
                    print "        <td class='data'>";
                    if (is_numeric($sharesToBuy))
                    {
                      print "            (" . $buyData->dps . " * " . $sharesToBuy . ") - " . $soldAnnualDps;
                    }
                    print "        </td>";
                    print "        <td class='data' $css>";
                    print "           " . formatCash($qtrDelta);
                    print "        </td>";
                    print "        <td class='data' $css>";
                    print "           " . formatCash($annualDelta);
                    print "        </td>";
                    print "    </tr>";
                }
            }
    
            print "    </tbody>";
            print "</table>";
            print "</fieldset>";
    
            print "<script>";
            print "    $(document).ready(function(){";
            print "        $('#data').DataTable();";
            print "    });";
            print "</script>";
        }
        
    }
?>
