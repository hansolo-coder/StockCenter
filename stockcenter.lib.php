<?php
    # constants
    $version = '1.8.0';
    $_SESSION['debug'] = "off";

    # first run code
    if (!file_exists("./data"))
    {
        initAdminDb();

        $f = fopen("./data/.htaccess", "w");
        fwrite($f, "<Files ~ \"\.sqlite$\">\n");
        fwrite($f, "	Order allow,deny\n");
        fwrite($f, "	Deny from all\n");
        fwrite($f, "</Files>\n");
    }
    
    
    # helper function to init a generic database for a generic "admin" user
    # used for test database and primary account
	function initAdminDb()
	{
	        include_once './classes/db.class.php';

		$db = new db();
		$db->fileName = "admin";
		$db->password = "admin";
		$db->init();

	        unset($db);
	}

	# db upgrade function
	function dbUpgrade($db)
	{
		$sqlCheck = "select * from stockData";
		$rsCheck = $db->prepare($sqlCheck);
		$rsCheck->execute();
		$columns = $rsCheck->columnCount();
		$rsCheck = null;
		
		# if the column count is incorrect...
		if($columns != "5")
		{
			# re-create the table in the new format
			$sqlDrop = "drop table stockData";
			$rsDrop = $db->prepare($sqlDrop);
			$rsDrop->execute();
			$rsDrop = null;
				
			$sqlCreate = "CREATE TABLE [stockData] (";
			$sqlCreate .= "[symbol] VARCHAR(10),";
			$sqlCreate .= "[market] VARCHAR(10),";
			$sqlCreate .= "[attribute] VARCHAR(100),";
			$sqlCreate .= "[value] VARCHAR(200),";
			$sqlCreate .= "[lastUpdated] INTEGER)";
			$rsCreate = $db->prepare($sqlCreate);
			$rsCreate->execute();
		}
		
		$sqlCheck = null;
		$sqlDrop = null;
		$sqlCreate = null;
		$rsCreate = null;
		$db = null;
		$conn = null;
		
		# add a setting for the database version
		$set = new setting();
		$set->settingName = "databaseVersion";
		$set->settingValue = "2";
		$set->settingDesc = "Database schema version";
		$set->insert();
	}
	
	
    # dividend earning chart
    function dividendEarningsChart($symbol)
    {
        # connect to the database
        if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbConnect: " . __LINE__ . "</span><br>";}
	    	
        include_once './classes/db.class.php';
	
        $conn = new db();
        $conn->fileName = $_SESSION['userId'];
        $db = $conn->connect();
    	
        # get the dividend data
        $sql = "SELECT * FROM (SELECT t.tDate, t.symbol, t.activity, t.shares, case when t.currency = a.accountcurrency then t.cost else t.cost * t.exchangerate end as cost, t.currency, case when t.currency = a.accountcurrency then t.tax else t.tax * t.exchangerate end as tax, t.exchangerate, a.accountcurrency FROM transactions t inner join accounts a on a.accountid = t.accountid where t.activity='DIVIDEND' AND t.symbol=:symbol ORDER BY t.tdate DESC LIMIT 10) ORDER BY tdate;";
        $rs = $db->prepare($sql);
        $rs->bindValue(':symbol', $symbol);
        $rs->execute();
        $rows = $rs->fetchall();

        $labels = '[';
        $data = '[';

        foreach($rows as $row)
        {
            $labels .= '"' . $row['tDate'] . '",';
            $data .= $row['cost'] . ',';
        }

        # trim off the last comma
        $labels = rtrim($labels, ",");
        $data = rtrim($data, ",");

        # cap off the dataset
        $labels .= "]";
        $data .= "]";

        # prepare the chart
        print "<script src='javascript/chart/Chart.js'></script>";
        print "<fieldset>";
        print "<legend>Last 10 Dividend Payments</legend>";
        print "<div style='width:100%; margins: auto;'>";
		print "	   <div>";
		print "		   <canvas id='canvas' height='15' width='100%'></canvas>";
		print "	   </div>";
		print "</div>";
        print "<script>";
        ?>
		var lineChartData = {
			labels : <?php print $labels; ?>,
			datasets : [
				{
					label: "Dividend Payments",
					fillColor : "rgba(220,220,220,0.2)",
					strokeColor : "rgba(220,220,220,1)",
					pointColor : "rgba(220,220,220,1)",
					pointStrokeColor : "#fff",
					pointHighlightFill : "#fff",
					pointHighlightStroke : "rgba(220,220,220,1)",
					data : <?php print $data; ?>
				}
                ]
		}

        window.onload = function(){
            var ctx = document.getElementById("canvas").getContext("2d");
            window.myLine = new Chart(ctx).Line(lineChartData, {
                responsive: true
            });
        }
        <?php
        print "</script>";
        print "</fieldset>";
        
        if ($_SESSION['debug'] == "on"){
        	print "<span class='debug'>dbDisconnect: " . __LINE__ . "</span><br>";
        }
        
        $rsStocks = null;
        $db = null;
        $conn= null;
    }
	
    
    # dividend report block
    function dividendReport($forYear)
    {
        print "<div class='spacer'></div>";
	    
        # historical dividend chart
        $labels = '[';
        $data = '[';

        $endYear = date('Y');
        $otherYear = $endYear;
        if ($forYear == $endYear)
        {
          $otherYear = $endYear - 1;
        }
        
        for($i = ($endYear - 9); $i <= ($endYear); $i++)
        {
            $labels .= '"' . $i . '",';
            $data .= annualDividends($i) . ',';
        }

        # trim off the last comma
        $labels = rtrim($labels, ",");
        $data = rtrim($data, ",");

        # cap off the dataset
        $labels .= "]";
        $data .= "]";

        # prepare the chart
        print "<script src='javascript/chart/Chart.js'></script>";
        print "<fieldset>";
        print "<legend>Annual Dividend Earnings</legend>";
        print "<div style='width:100%; margins: auto;'>";
        print "	   <div>";
        print "		   <canvas id='canvas' height='15' width='100%'></canvas>";
        print "	   </div>";
        print "</div>";
        print "<script>";
        ?>
        var lineChartData = {
        	labels : <?php print $labels; ?>,
        	datasets : [
        		{
        			label: "Dividend Payments",
        			fillColor : "rgba(220,220,220,0.2)",
        			strokeColor : "rgba(220,220,220,1)",
        			pointColor : "rgba(220,220,220,1)",
        			pointStrokeColor : "#fff",
        			pointHighlightFill : "#fff",
        			pointHighlightStroke : "rgba(220,220,220,1)",
        			data : <?php print $data; ?>
        		}
                ]
        }

        window.onload = function(){
            var ctx = document.getElementById("canvas").getContext("2d");
            window.myLine = new Chart(ctx).Line(lineChartData, {
                responsive: true
            });
        }
        <?php
        print "</script>";
        print "</fieldset>";


        include_once './classes/db.class.php';

        if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbConnect: " . __LINE__ . "</span><br>";}

        $conn = new db();
        $conn->fileName = $_SESSION['userId'];
        $db = $conn->connect();
        
        # begin this year dividend report
        $totalDividends = 0;

        $sqlStockList = "SELECT * FROM stocks ORDER BY symbol";
        $rsStockList = $db->prepare($sqlStockList);
        $rsStockList->execute();
        $stockList = $rsStockList->fetchall();

        $rsStockList = null;

        print "<div class='spacer'></div>";
        print "<fieldset>";
        print "    <legend>";
        print "        " . $forYear . " Dividend Report";
        print " (See <a href='" . htmlentities($_SERVER['PHP_SELF']) . "?action=dividendReport&year=" . $otherYear . "'>" . $otherYear . "</a>)";
        print "    </legend>";
        print "<table class='data'>";
        print "    <tr>";
        print "        <th class='data' width='10%'>";
        print "            Symbol";
        print "        </th>";
        print "        <th class='data' width='75%'>";
        print "            Company Name";
        print "        </th>";
        print "        <th class='data' width='15'>";
        print "            Dividends Earned";
        print "        </th>";
        print "    </tr>";

        $totalDividends = 0;

        foreach($stockList as $rowStockList)
        {
            if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbConnect: " . __LINE__ . "</span><br>";}
        	
            $conn = new db();
            $conn->fileName = $_SESSION['userId'];
            $db = $conn->connect();
	        
            $sql = "SELECT sum(shares) as s FROM transactions where activity IN ('BUY','BONUS','SPLIT') AND symbol=:symbol";
            $rs = $db->prepare($sql);
            $rs->bindValue(':symbol', $rowStockList['symbol']);
            $rs->execute();
            $row = $rs->fetch();
            $boughtShares = $row['s'];

            $sql = "SELECT sum(shares) as s FROM transactions where activity='SELL' AND symbol=:symbol";
            $rs = $db->prepare($sql);
            $rs->bindValue(':symbol', $rowStockList['symbol']);
            $rs->execute();
            $row = $rs->fetch();
            $soldShares = $row['s'];

            $sqlDividend = "SELECT sum(cost) as s, currency FROM transactions where activity='DIVIDEND' AND symbol=:symbol AND tDate >= '" . $forYear . "-01-01' AND tDate <= '" . $forYear . "-12-31' group by currency";
            $rsDividend = $db->prepare($sqlDividend);
            $rsDividend->bindValue(':symbol', $rowStockList['symbol']);
            $rsDividend->execute();
            $rowDividend = $rsDividend->fetch();
            $dividends = $rowDividend['s'];
            $divcurrency = $rowDividend['currency'];
            if ($divcurrency == '') {
               $divcurrency = $_SESSION['DefaultCurrency'];
            }

        	if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbDisconnect: " . __LINE__ . "</span><br>";}
        	$row = null;
        	$rowDividend = null;
        	$rs = null;
        	$rsDividend = null;
        	$rsStockList = null;
        	$db = null;
        	$conn = null;
        	
        	include_once './classes/tc/stockData.class.php';

        	$sData = new stockData();
        	$sData->symbol = $rowStockList['symbol'];
        	$sData->select();

            if ($dividends > 0)
            {
                print "<tr>";
                print "    <td class='data'>";
                print "        " . $sData->symbol;
                print "    </td>";
                print "    <td class='data'>";
                print "        " . $sData->name;
                print "    </td>";
                print "    <td class='data' style='text-align: right;'>";
                print "        " . formatCashWCurr($dividends, $divcurrency);
                print "    </td>";
                print "</tr>";

                $totalDividends = toCash($totalDividends + $dividends);
            }
        }

        print "</table>";
        print "<br>";
        print "<span class='heading'>Total Dividends Earned: " . formatCash($totalDividends) . "</span>";
        print "</fieldset>";


        # begin cumulative dividend report
        $totalDividends = 0;

        if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbConnect: index.php " . __LINE__ . "</span><br>";}

        include_once './classes/db.class.php';
        
        $conn = new db();
        $conn->fileName = $_SESSION['userId'];
        $db=$conn->connect();

        $sqlStockList = "SELECT * FROM stocks ORDER BY symbol";
        $rsStockList = $db->prepare($sqlStockList);
        $rsStockList->execute();
        $rows = $rsStockList->fetchAll();

        $rsStockList = null;
        
        print "<div class='spacer'></div>";
        print "<fieldset>";
        print "    <legend>";
        print "        Cumulative Dividend Report (Current Holdings)";
        print "    </legend>";
        print "<table class='data'>";
        print "    <tr>";
        print "        <th class='data' width='10%'>";
        print "            Symbol";
        print "        </th>";
        print "        <th class='data' width='75%'>";
        print "            Company Name";
        print "        </th>";
        print "        <th class='data' width='15'>";
        print "            Dividends Earned";
        print "        </th>";
        print "    </tr>";

        $totalDividends = 0;

        foreach ($rows as $rowStockList)
        {
            if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbConnect: index.php " . __LINE__ . "</span><br>";}

            $conn = new db();
	    $conn->fileName = $_SESSION['userId'];
	    $db = $conn->connect();
	        
            $sql = "SELECT sum(shares) as s FROM transactions where activity IN ('BUY','BONUS','SPLIT') AND symbol=:symbol";
            $rs = $db->prepare($sql);
            $rs->bindValue(':symbol', $rowStockList['symbol']);
            $rs->execute();
            $row = $rs->fetch();
            $boughtShares = $row['s'];

            $sql = "SELECT sum(shares) as s FROM transactions where activity='SELL' AND symbol=:symbol";
            $rs = $db->prepare($sql);
            $rs->bindValue(':symbol', $rowStockList['symbol']);
            $rs->execute();
            $row = $rs->fetch();
            $soldShares = $row['s'];

            $sqlDividend = "SELECT sum(cost) as s, currency FROM transactions where activity='DIVIDEND' AND symbol=:symbol";
            $rsDividend = $db->prepare($sqlDividend);
            $rsDividend->bindValue(':symbol', $rowStockList['symbol']);
            $rsDividend->execute();
            $rowDividend = $rsDividend->fetch();
            $dividends = $rowDividend['s'];
            $divcurrency = $rowDividend['currency'];
            if ($divcurrency == '') {
               $divcurrency = $_SESSION['DefaultCurrency'];
            }

        	if ($_SESSION['debug'] == "on"){
        		print "<span class='debug'>dbDisconnect: " . __LINE__ . "</span><br>";
        	}
        	
        	$rowDividend = null;
        	$rsDividend = null;
        	$row = null;
        	$rs = null;
        	$rsStockList = null;
        	$db = null;
        	$conn = null;
        	
        	$sData->symbol = $rowStockList['symbol'];
        	$sData->select();

            if ((($boughtShares - $soldShares) > 0))
            {
                print "<tr>";
                print "    <td class='data'>";
                print "        " . $sData->symbol;
                print "    </td>";
                print "    <td class='data'>";
                print "        " . $sData->name;
                print "    </td>";
                print "    <td class='data' style='text-align: right;'>";
                print "        " . formatCashWCurr($dividends, $divcurrency);
                print "    </td>";
                print "</tr>";

                $totalDividends = toCash($totalDividends + $dividends);
            }
        }

        print "</table>";
        print "<br>";
        print "<span class='heading'>Total Dividends Earned: " . formatCash($totalDividends) . "</span>";
        print "</fieldset>";


        # begin all-time dividend report
        $totalDividends = 0;

        if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbConnect: " . __LINE__ . "</span><br>";}

        include_once './classes/db.class.php';
        
        $conn = new db();
        $conn->fileName = $_SESSION['userId'];
        $db=$conn->connect();
        
        $sqlStockList = "SELECT * FROM stocks ORDER BY symbol";
        $rsStockList = $db->prepare($sqlStockList);
        $rsStockList->execute();
        $rows = $rsStockList->fetchAll();
        
        $rsStockList = null;

        print "<div class='spacer'></div>";
        print "<fieldset>";
        print "    <legend>";
        print "        All-Time Dividend Report (Includes Liquidated Holdings)";
        print "    </legend>";
        print "<table class='data'>";
        print "    <tr>";
        print "        <th class='data' width='10%'>";
        print "            Symbol";
        print "        </th>";
        print "        <th class='data' width='75%'>";
        print "            Company Name";
        print "        </th>";
        print "        <th class='data' width='15'>";
        print "            Dividends Earned";
        print "        </th>";
        print "    </tr>";

        $totalDividends = 0;

        foreach ($rows as $rowStockList)
        {
        	if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbConnect: index.php " . __LINE__ . "</span><br>";}
        	
        	$conn = new db();
	        $conn->fileName = $_SESSION['userId'];
	        $db = $conn->connect();
	        
        	$sql = "SELECT sum(shares) as s FROM transactions where activity IN ('BUY','BONUS','SPLIT') AND symbol=:symbol";
            $rs = $db->prepare($sql);
            $rs->bindValue(':symbol', $rowStockList['symbol']);
            $rs->execute();
            $row = $rs->fetch();
            $boughtShares = $row['s'];

            $sql = "SELECT sum(shares) as s FROM transactions where activity='SELL' AND symbol=:symbol";
            $rs = $db->prepare($sql);
            $rs->bindValue(':symbol', $rowStockList['symbol']);
            $rs->execute();
            $row = $rs->fetch();
            $soldShares = $row['s'];

            $sqlDividend = "SELECT sum(cost) as s, currency FROM transactions where activity='DIVIDEND' AND symbol=:symbol";
            $rsDividend = $db->prepare($sqlDividend);
            $rsDividend->bindValue(':symbol', $rowStockList['symbol']);
            $rsDividend->execute();
            $rowDividend = $rsDividend->fetch();
            $dividends = $rowDividend['s'];
            $divcurrency = $rowDividend['currency'];
            if ($divcurrency == '') {
               $divcurrency = $_SESSION['DefaultCurrency'];
            }

        	if ($_SESSION['debug'] == "on"){
        		print "<span class='debug'>dbDisconnect: " . __LINE__ . "</span><br>";
        	}
        	
        	$rowDividend = null;
        	$rsDividend = null;
        	$row = null;
        	$rs = null;
        	$rsStockList = null;
        	$db = null;
        	$conn = null;
        	
        	$sData->symbol = $rowStockList['symbol'];
        	$sData->select();

        	if($dividends > 0)
        	{
        		print "<tr>";
	            print "    <td class='data'>";
	            print "        " . $sData->symbol;
	            print "    </td>";
	            print "    <td class='data'>";
	            print "        " . $sData->name;
	            print "    </td>";
	            print "    <td class='data' style='text-align: right;'>";
	            print "        " . formatCashWCurr($dividends, $divcurrency);
	            print "    </td>";
	            print "</tr>";
        	}
        	
            $totalDividends = toCash($totalDividends + $dividends);
        }

        print "</table>";
        print "<br>";
        print "<span class='heading'>Total Dividends Earned: " . formatCash($totalDividends) . "</span>";
        print "</fieldset>";
	}
	
	
    # checks last time stock data was updated and pulls new data if needed
	function getData($symbol)
	{
		if ($_SESSION['debug'] == "on"){
			print "<span class='debug'>getData($symbol)</span><br>";
		}
			
		include_once './classes/tc/setting.class.php';

		$s = new setting();
		$s->settingName = 'refreshTime';
		$s->select();

        	include_once './classes/tc/stockData.class.php';

		$sData = new stockData();
		$sData->symbol = $symbol;
		$sData->select();

        	include_once './classes/tc/stocks.class.php';

		$stData = new stocks();
		$stData->symbol = $symbol;
		$stData->select();

		# update data if its been more than x minutes
		if ((time() - $sData->lastUpdated > (60 * $s->settingValue)) && $stData->skipLookup == 0)
		{
			pullFromYahoo($symbol);
		}
	}


	# application landing page
    function homePage()
    {
        if ($_SESSION['debug'] == "on"){print "<span class='debug'>homePage()</span><br>\n";}

        include_once './classes/pageHeader.class.php';
        $header = new pageHeader();
        $header->display();
        overview(NULL);
    }


	# generates a div block for success messages, etc.
    function message($type, $message)
    {
        if ($_SESSION['debug'] == "on"){print "<span class='debug'>message($type, $message)</span><br>\n";}

        print "<div class='" . $type . "'>\n";
        print "    " . $message . "\n";
        print "</div>\n";
    }
    
    
    function overview($showForAccount)
    {
	if ($_SESSION['debug'] == "on"){
		print "<span class='debug'>overview</span><br>";
	}
	if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbConnect: " . __LINE__ . "</span><br>";}
    	
	# Get the percentage-range for which to mark a position as "Unchanged" in value
	include_once './classes/tc/setting.class.php';
	$set = new setting();
	$set->settingName = "chgPctMarkUnchanged";
	$set->select();
	$chgPctMarkUnchanged = $set->settingValue + 0;

	$conn = new db();
	$conn->fileName = $_SESSION['userId'];
	$db=$conn->connect();

	$sqlStockList = "SELECT * FROM stocks ORDER BY symbol";
	$rsStockList = $db->prepare($sqlStockList);
	$rsStockList->execute();
	$stockList = $rsStockList->fetchAll();

	if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbDisconnect: " . __LINE__ . "</span><br>";}
        
	$rsStockList = NULL;
	$sqlStockList = NULL;
	$db = NULL;
	$conn = NULL;
        
	dividendEvents();
	print "<div class='spacer'></div>\n";
	print "<fieldset>\n";
        print "<legend>Portfolio Overview</legend>\n";
        print "<table class='display' id='overview'>\n";
	print "    <thead>\n";
        print "    <tr>\n";
        print "        <th class='data' width='20.6%'>\n";
        print "            Stock\n";
        print "        </th>\n";
        print "        <th class='data' width='16.6%'>\n";
        print "            Current Price\n";
        print "        </th>\n";
        print "        <th class='data' width='10.6%'>\n";
        print "            Shares Owned\n";
        print "        </th>\n";
        print "        <th class='data' width='16.6%'>\n";
        print "            Currently Invested\n";
        print "        </th>\n";
        print "        <th class='data' width='16.6%'>\n";
        print "            Current Value\n";
        print "        </th>\n";
        print "        <th class='data' width='16.6%'>\n";
        print "            Dividends Earned\n";
        print "        </th>\n";
        print "    </tr>\n";
	print "    </thead>\n";
	print "    <tbody>\n";

        $totalCurrentlyInvested = 0;
        $totalCurrentValue = 0;
        $totalDividends = 0;
        foreach ($stockList as $rowStocklist)
        {
            getData($rowStocklist['symbol']);

            include_once './classes/tc/stockData.class.php';

            $sData = new stockData();
            $sData->symbol = $rowStocklist['symbol'];
            $sData->select();

            $currentPrice = $sData->currentPrice2;
            $yearHigh = $sData->yearHigh;
            $yearLow = $sData->yearLow;
            $dividendYield = $sData->yield;
            $dps = $sData->dps;
            $exDividendDate = $sData->xDate;
            $payDate = $sData->pDate;
            $eps = $sData->eps;
            $name = $sData->name;
            $lastUpdated = $sData->lastUpdated;
            $scurrency = $sData->currency;
            if ($scurrency == '') {
               $scurrency = $_SESSION['DefaultCurrency'];
            }

            $sData = null;
            
            $dataSource = "Updates in " . number_format((float)($_SESSION['refreshTime'] - ((time() - $lastUpdated) / 60)), 0, '.', '') . " minutes";

	    if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbConnect: index.php " . __LINE__ . "</span><br>";}
	    	
	    include_once './classes/db.class.php';
	        
	    $conn = new db();
	    $conn->fileName = $_SESSION['userId'];
	    $db=$conn->connect();

            $sql = "SELECT sum(shares) as s FROM transactions where activity IN ('BUY','BONUS','SPLIT') AND symbol=:symbol AND (:accountId IS NULL OR :accountId = accountId)";
            $rs = $db->prepare($sql);
            $rs->bindValue(':symbol', $rowStocklist['symbol']);
            $rs->bindValue(':accountId', $showForAccount);
            $rs->execute();
            $row = $rs->fetch();
            $boughtShares = $row['s'];
            
            $row = null;
            $rs = null;

            $sql = "SELECT sum(shares) as s FROM transactions where activity='SELL' AND symbol=:symbol AND (:accountId IS NULL OR :accountId = accountId)";
            $rs = $db->prepare($sql);
            $rs->bindValue(':symbol', $rowStocklist['symbol']);
            $rs->bindValue(':accountId', $showForAccount);
            $rs->execute();
            $row = $rs->fetch();
            $soldShares = $row['s'];

            $row = null;
            $rs = null;
            
            $sql = "SELECT cost, shares FROM transactions where activity IN ('BUY','BONUS','SPLIT') AND symbol=:symbol";
            $rs = $db->prepare($sql);
            $rs->bindValue(':symbol', $rowStocklist['symbol']);
            $rs->execute();
            $rows = $rs->fetchAll();
			
            $rs = null;
			
            $totalSpent = 0;

            foreach ($rows as $row)
            {
                $sale = $row['shares'] * $row['cost'];
                $totalSpent = $totalSpent + $sale;
            }

            $sql = "SELECT cost, shares FROM transactions where activity='SELL' AND symbol=:symbol";
            $rs = $db->prepare($sql);
            $rs->bindValue(':symbol', $rowStocklist['symbol']);
            $rs->execute();
            $rows = $rs->fetchAll();
			
            $rs = null;
			
            $totalSales = 0;

            foreach ($rows as $row)
            {
                $sale = $row['shares'] * $row['cost'];
                $totalSales = $totalSales + $sale;
            }

            $sql = "SELECT sum(cost) as s, currency FROM transactions where activity='DIVIDEND' AND symbol=:symbol group by currency";
            $rs = $db->prepare($sql);
            $rs->bindValue(':symbol', $rowStocklist['symbol']);
            $rs->execute();
            $row = $rs->fetch();
            $dividends = $row['s'];
            $divcurrency = $row['currency'];
            if ($divcurrency == '') {
               $divcurrency = $scurrency;
            }

            $row = null;
            $rs = null;
            
            if($dividends == '')
            {
                $dividends = 0;
            }

            $sql = "SELECT sum(cost) as s FROM transactions where activity='FEE' AND symbol=:symbol group by currency";
            $rs = $db->prepare($sql);
            $rs->bindValue(':symbol', $rowStocklist['symbol']);
            $rs->execute();
            $row = $rs->fetch();
            $fees = $row['s'];

            $row = null;
            $rs = null;
            
            if (($boughtShares - $soldShares) > 0)
            {
                $pps = ($totalSpent / $boughtShares);
            }
            else
            {
                $pps = 0;
            }

            if ((($boughtShares - $soldShares) > 0))
            {
                $currentValue = ($currentPrice * ($boughtShares - $soldShares));
                $currentlyInvested = toCash($totalSpent) - toCash($totalSales);

                print "<tr>\n";
                print "    <td class='data'>\n";
                print "        " . $rowStocklist['symbol'];
                print "    </td>\n";
                print "    <td class='data'>\n";
                print "       " . formatCashWCurr($currentPrice, $scurrency);
                print "    </td>\n";
                print "    <td class='data'>\n";
                print "        " . ($boughtShares - $soldShares);
                print "    </td>\n";
                print "    <td class='data'>\n";
                print "        " . formatCashWCurr($currentlyInvested, $scurrency);
                print "    </td>\n";

                if($currentlyInvested > 0 && (abs($currentlyInvested - $currentValue) / $currentlyInvested < $chgPctMarkUnchanged))
                {
                    print "    <td class='data' style='background-color: #F3E5AB;'>\n"; // Dark Yellow #9B870C / Pale Yellow #F3E5AB
                }
                else if($currentValue > $currentlyInvested)
                {
                    print "    <td class='data' style='background-color: #AFFFAB;'>\n";
                }
                else
                {
                    print "    <td class='data' style='background-color: #FFB6AB;'>\n";
                }

                print "        " . formatCashWCurr($currentValue, $scurrency);
                print "    </td>\n";
                print "    <td class='data'>\n";
                print "        " . formatCashWCurr($dividends, $divcurrency);
                print "    </td>\n";
                print "</tr>\n";

                $totalCurrentlyInvested = toCash($totalCurrentlyInvested + $currentlyInvested);
                $totalCurrentValue = toCash($totalCurrentValue + $currentValue);
                $totalDividends = toCash($totalDividends + $dividends);
            }
            
            if ($_SESSION['debug'] == "on"){
            	print "<span class='debug'>dbDisconnect: index.php " . __LINE__ . "</span><br>";
            }
            
            $row = null;
            $rs = null;
            $db = null;
            $conn = null;
       }

        if(toCash($totalCurrentValue) > toCash($totalCurrentlyInvested))
        {
            $css = "background-color: #AFFFAB;";
        }
        else
        {
            $css = "background-color: #FFB6AB;";
        }

	print "    </tbody>\n";
	print "</table>\n";
	print "<div class='spacer'></div>\n";
	print "<table class='data' id='totals'>\n";
        print "    <tr>\n";
        print "        <td class='data' width='48.5%' style='text-align: right; background-color: #E6E6E6; font-weight: bold;' colspan='3'>\n";
        print "            Totals:\n";
        print "        </td>\n";
        print "        <td class='data' width='16.6%' style='taxt-align: right;'>\n";
        print "            " . formatCash($totalCurrentlyInvested);
        print "        </td>\n";
        print "        <td class='data' width='16.6%' style='taxt-align: right; $css'>\n";
        print "            " . formatCash($totalCurrentValue);
        print "        </td>\n";
        print "        <td class='data' width='16.6%'>\n";
        print "            " . formatCash($totalDividends);
        print "        </td>\n";
        print "</table>\n";
        print "</fieldset>\n";

		print "<script>\n";
		print "    $(document).ready(function(){\n";
		print "        $('#overview').DataTable();\n";
		print "    });\n";
		print "</script>\n";

        print "<div class='spacer'></div>\n";

        print "<table width='100%'>\n";
        print "    <tr>\n";
        print "        <td width='50%'>\n";
        
        holdingsValueChart();

        print "        </td>\n";
        print "        <td width='50%'>\n";

        sharesOwnedChart();

        print "        </td>\n";
        print "    </tr>\n";
        print "</table>\n";
        
        print "<script>\n";
        print "    function start()\n";
        print "    {\n";
        print "        showSharesOwnedChart();\n";
        print "        showHoldingsValueChart();\n";
        print "    }\n";
        print "    window.onload = start();\n";
        print "</script>\n";
    }
    
    
    # shares value chart
    function holdingsValueChart()
    {
        if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbConnect: " . __LINE__ . "</span><br>";}
    	
        include_once './classes/db.class.php';

        $conn = new db();
        $conn->fileName = $_SESSION['userId'];
        $db = $conn->connect();

        $sqlStocks = "SELECT symbol FROM stocks order by symbol";
        $rsStocks = $db->prepare($sqlStocks);
        $rsStocks->execute();
        $stocks = $rsStocks->fetchAll();

        if ($_SESSION['debug'] == "on"){
        	print "<span class='debug'>dbDisconnect: " . __LINE__ . "</span><br>";
        }
        
        $rsStocks = null;
        $db = null;
        $conn= null;
        
        $labels = '[';
        $data = '[';

        foreach($stocks as $stock)
        {
        	include_once './classes/tc/stockData.class.php';

        	$sData = new stockData();
        	$sData->symbol = $stock['symbol'];
        	$sData->select();

        	$currentPrice = $sData->currentPrice2;

	        if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbConnect: " . __LINE__ . "</span><br>";}
	    	
	        $conn = new db();
	        $conn->fileName = $_SESSION['userId'];
	        $db = $conn->connect();
	        
        	$sql = "SELECT sum(shares) as s FROM transactions where activity IN ('BUY','BONUS','SPLIT') AND symbol=:symbol";
            $rs = $db->prepare($sql);
            $rs->bindValue(':symbol', $stock['symbol']);
            $rs->execute();
            $row = $rs->fetch();
            $boughtShares = $row['s'];

            $sql = "SELECT sum(shares) as s FROM transactions where activity='SELL' AND symbol=:symbol";
            $rs = $db->prepare($sql);
            $rs->bindValue(':symbol', $stock['symbol']);
            $rs->execute();
            $row = $rs->fetch();
            $soldShares = $row['s'];

            $ownedShares = ($boughtShares - $soldShares);
            $currentValue = ($ownedShares * $currentPrice);


            if($ownedShares > 0)
            {
                $labels .= '"' . $stock['symbol'] . '",';
                $data .= toCash($currentValue) . ',';
            }
            
            if ($_SESSION['debug'] == "on"){
            	print "<span class='debug'>dbConnect: " . __LINE__ . "</span><br>";
            }
            
            $rs = null;
            $db = null;
            $conn = null;
		}

        # trim off the last comma
        $labels = rtrim($labels, ",");
        $data = rtrim($data, ",");

        # cap off the dataset
        $labels .= "]";
        $data .= "]";

        # prepare the chart
        print "<script src='javascript/chart/Chart.js'></script>";
        print "<fieldset>";
        print "<legend>Portfolio Value</legend>";
        print "<div style='width:100%; margins: auto;'>";
		print "	   <div>";
		print "		   <canvas id='value' height='50' width='100%'></canvas>";
		print "	   </div>";
		print "</div>";
        print "<script>";
        ?>
		var valueChartData = {
			labels : <?php print $labels; ?>,
			datasets : [
				{
					label: "Value",
					fillColor : "#eaeaea",
					strokeColor : "#979797",
					pointColor : "rgba(220,220,220,1)",
					pointStrokeColor : "#fff",
					pointHighlightFill : "#fff",
					pointHighlightStroke : "rgba(220,220,220,1)",
					data : <?php print $data; ?>
				}
                ]
		}

        function showHoldingsValueChart(){
            var valuectx = document.getElementById("value").getContext("2d");
            window.myValue = new Chart(valuectx).Bar(valueChartData, {
                responsive: true
            });
        }
        <?php
        print "</script>";
        print "</fieldset>";
    }
    
    
    # shares owned chart
    function sharesOwnedChart()
    {
        if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbConnect: " . __LINE__ . "</span><br>";}
    	
        include_once './classes/db.class.php';

        $conn = new db();
        $conn->fileName = $_SESSION['userId'];
        $db = $conn->connect();
    	
    	$sqlStocks = "SELECT symbol FROM stocks order by symbol";
        $rsStocks = $db->prepare($sqlStocks);
        $rsStocks->execute();
        $stocks = $rsStocks->fetchAll();

        if ($_SESSION['debug'] == "on"){
        	print "<span class='debug'>dbDisconnect: " . __LINE__ . "</span><br>";
        }
        
        $rsStocks = null;
        $db = null;
        $conn= null;
        
        $labels = '[';
        $data = '[';

        foreach($stocks as $stock)
        {
	        if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbConnect: " . __LINE__ . "</span><br>";}
	    	
	        include_once './classes/db.class.php';
	
	        $conn = new db();
	        $conn->fileName = $_SESSION['userId'];
	        $db = $conn->connect();
	        	
	        $sql = "SELECT sum(shares) as s FROM transactions where activity IN ('BUY','BONUS','SPLIT') AND symbol=:symbol";
            $rs = $db->prepare($sql);
            $rs->bindValue(':symbol', $stock['symbol']);
            $rs->execute();
            $row = $rs->fetch();
            $boughtShares = $row['s'];

            $sql = "SELECT sum(shares) as s FROM transactions where activity='SELL' AND symbol=:symbol";
            $rs = $db->prepare($sql);
            $rs->bindValue(':symbol', $stock['symbol']);
            $rs->execute();
            $row = $rs->fetch();
            $soldShares = $row['s'];

            $ownedShares = $boughtShares - $soldShares;

            if($ownedShares > 0)
            {
                $labels .= '"' . $stock['symbol'] . '",';
                $data .= $ownedShares . ',';
            }
            
            if ($_SESSION['debug'] == "on"){
            	print "<span class='debug'>dbDisconnect: " . __LINE__ . "</span><br>";
            }
            
            $rsStocks = null;
            $db = null;
            $conn= null;
        }

        # trim off the last comma
        $labels = rtrim($labels, ",");
        $data = rtrim($data, ",");

        # cap off the dataset
        $labels .= "]";
        $data .= "]";

        # prepare the chart
        print "<script src='javascript/chart/Chart.js'></script>";
        print "<fieldset>";
        print "<legend>Share Allocation</legend>";
        print "<div style='width:100%; margins: auto;'>";
		print "	   <div>";
		print "		   <canvas id='shares' height='50' width='100%'></canvas>";
		print "	   </div>";
		print "</div>";
        print "<script>";
        ?>
		var shareChartData = {
			labels : <?php print $labels; ?>,
			datasets : [
				{
					label: "Share Allocation",
					fillColor : "#eaeaea",
					strokeColor : "#979797",
					pointColor : "#cfcfcf",
					pointStrokeColor : "#fff",
					pointHighlightFill : "#fff",
					pointHighlightStroke : "rgba(220,220,220,1)",
					data : <?php print $data; ?>
				}
                ]
		}

        function showSharesOwnedChart(){
            var sharectx = document.getElementById("shares").getContext("2d");
            window.myShare = new Chart(sharectx).Bar(shareChartData, {
                responsive: true
            });
        }
        <?php
        print "</script>";
        print "</fieldset>";
    }
    
    
    # portfolio signals block
	function signals()
	{
    	if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbConnect: " . __LINE__ . "</span><br>";}
    	
    	include_once './classes/db.class.php';
    	
    	$conn = new db();
        $conn->fileName = $_SESSION['userId'];
        $db=$conn->connect();
        
		$sqlSettings = "SELECT * FROM settings WHERE settingName='sellTrigger'";
		$rsSettings  = $db->prepare($sqlSettings);
		$rsSettings->execute();
		$rowSettings = $rsSettings->fetch();
		$sellTrigger = $rowSettings['settingValue'];

		$sql = "SELECT symbol FROM stocks ORDER BY symbol";
		$rs = $db->prepare($sql);
		$rs->execute();
		$rows = $rs->fetchAll();
		
		if ($_SESSION['debug'] == "on"){
			print "<span class='debug'>dbDisconnect: " . __LINE__ . "</span><br>";
		}
		 
		$rs = null;
		$db = null;
		$conn = null;
		

	print "<div class='spacer'></div>";
        print "<fieldset>";
        print "<legend>Legend</legend>";
        print "<table class='data'>";
        print "    <tr>";
        print "        <th class='data'>";
        print "            Hold";
        print "        </th>";
        print "        <th class='data'>";
        print "            High Sell";
        print "        </th>";
        print "        <th class='data'>";
        print "            Growth Sell";
        print "        </th>";
        print "        <th class='data'>";
        print "            Below Average Buy";
        print "        </th>";
        print "    </tr>";
        print "    <tr>";
        print "        <td class='data' style='background-color: #ABD9FF;'>";
        print "            No triggers apply";
        print "        </td>";
        print "        <td class='data' style='background-color: #FFB6AB;'>";
        print "            Price above 52 wk high";
        print "        </td>";
        print "        <td class='data' style='background-color: #FFB6AB;'>";
        print "            Growth exceeds sell trigger percentage";
        print "        </td>";
        print "        <td class='data' style='background-color: #AFFFAB;'>";
        print "            Current price lower than average paid";
        print "        </td>";
        print "    </tr>";
        print "</table>";
        print "</fieldset>";
        print "<div class='spacer'></div>";
		print "<fieldset>";
        print "<legend>Signals</legend>";
		print "<table class='display' id='data'>";
		print "    <thead>";
		print "    <tr>";
		print "        <th class='data'>";
		print "            Stock";
		print "        </th>";
		print "        <th class='data'>";
		print "            Ask Price";
		print "        </th>";
		print "        <th class='data'>";
		print "            Average Paid";
		print "        </th>";
		print "        <th class='data'>";
		print "            52 Week Low";
		print "        </th>";
		print "        <th class='data'>";
		print "            52 Week High";
		print "        </th>";
		print "        <th class='data'>";
		print "            Growth Target (" . ($sellTrigger * 100) . "%)";
		print "        </th>";
		print "        <th class='data'>";
		print "            Signal";
		print "        </th>";
		print "    </tr>";
		print "    </thead>";
		print "    <tbody>";

		foreach ($rows as $row)
		{
			# make sure the data is fresh
			// getData($row['symbol']);

			include_once './classes/tc/stockData.class.php';

			$sData = new stockData();
			$sData->symbol = $row['symbol'];
			$sData->select();

	    	if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbConnect: " . __LINE__ . "</span><br>";}
	    	
	    	include_once './classes/db.class.php';
	    	
	    	$conn = new db();
	        $conn->fileName = $_SESSION['userId'];
	        $db=$conn->connect();
        
			# calculate bought share count
			$sqlBought = "SELECT sum(shares) as s FROM transactions where activity IN ('BUY','BONUS','SPLIT') AND symbol=:symbol";
			$rsBought = $db->prepare($sqlBought);
            $rsBought->bindValue(':symbol', $row['symbol']);
			$rsBought->execute();
			$rowBought = $rsBought->fetch();
			$boughtShares = $rowBought['s'];

			# calculate sold share count
			$sqlSold = "SELECT sum(shares) as s FROM transactions where activity='SELL' AND symbol=:symbol";
			$rsSold = $db->prepare($sqlSold);
            $rsSold->bindValue(':symbol', $row['symbol']);
			$rsSold->execute();
			$rowSold = $rsSold->fetch();
			$soldShares = $rowSold['s'];

			# calculate total spent
			$sqlTs = "SELECT cost, shares FROM transactions where activity IN ('BUY','BONUS','SPLIT') AND symbol=:symbol";
			$rsTs = $db->prepare($sqlTs);
            $rsTs->bindValue(':symbol', $row['symbol']);
			$rsTs->execute();

			$totalSpent = 0;

			while($rowTs = $rsTs->fetch())
			{
				$sale = $rowTs['shares'] * $rowTs['cost'];
				$totalSpent = $totalSpent + $sale;
			}

			# calculate price per share
			if (($boughtShares - $soldShares) > 0)
			{
				$pps = ($totalSpent / $boughtShares);
				$pps = toCash($pps);
			}
			else
			{
				$pps = 0;
			}

			# calculate the signal
			if ($sData->currentPrice2 < $pps) # cheaper than average price paid
			{
				$signalColor = "#AFFFAB";
				$signal = "BELOW AVERAGE BUY (" . tocash($pps - $sData->currentPrice2) . " cheaper)";
			}
			elseif ($sData->currentPrice2 >= $sData->yearHigh) # current price is at 52 week high
			{
				$signalColor = "#FFB6AB";
				$signal = "HIGH SELL (+" . (toCash($sData->currentPrice2) - (toCash($pps))) . " vs. avg paid)";
			}
			elseif ((toCash(($sellTrigger * $pps)) + $pps) < $sData->currentPrice2)
			{
				$signalColor = "#FFB6AB";
				$signal = "GROWTH SELL > " . ($sellTrigger * 100) . "%";
			}
			else
			{
				$signalColor = "#ABD9FF";
				$signal = "HOLD";
			}

			if ((($boughtShares - $soldShares) > 0))
			{
				print "    <tr>";
				print "        <td class='data'>";
				print "            " . $sData->symbol;
				print "        </td>";
				print "        <td class='data'>";
				print "            " . toCash($sData->currentPrice2);
				print "        </td>";
				print "        <td class='data'>";
				print "            " . toCash($pps);
				print "        </td>";
				print "        <td class='data'>";
				print "            " . toCash($sData->yearLow);
				print "        </td>";
				print "        <td class='data'>";
				print "            " . toCash($sData->yearHigh);
				print "        </td>";
				print "        <td class='data'>";
				print "            " . toCash(($sellTrigger * $pps) + $pps);
				
				if ($sData->currentPrice2 - (($sellTrigger * $pps) + $pps) > 0)
				{
				    print "            <span style='color: #0BA800;'>";
				}
				else
				{
				    print "            <span style='color: #FF2F14;'>";
				}
				
				print "                (" . toCash($sData->currentPrice2 - (($sellTrigger * $pps) + $pps)) . ")";
				print "            </span>";
				print "        </td>";
				print "        <td class='data' style='background-color: " . $signalColor . ";'>";
				print "            " . $signal;
				print "        </td>";
				print "    </tr>";
			}
		}

		print "    </tbody>";
		print "</table>";
        print "</fieldset>";

        print "<script>";
        print "    $(document).ready(function() {";
        print "        $('#data').DataTable();";
        print "    } );";
        print "</script>";
        
        if ($_SESSION['debug'] == "on"){
        	print "<span class='debug'>dbDisconnect: " . __LINE__ . "</span><br>";
        }
        	
        $rsBought = null;
        $rsSold = null;
        $rsTs = null;
        $db = null;
        $conn = null;
	}
    
	
	# gets new data from yahoo finance and saves to database
	function pullFromYahoo($symbol)
	{
		if ($_SESSION['debug'] == "on"){print "<span class='debug'>pullFromYahoo($symbol)</span><br>";}

		$data = new stockData();
		$data->symbol = $symbol;
		$data->delete();
		$data->insert();
	}

	# log daily status
	function logDailyStatus($action, $userId, $access) {
    	  if ($_SESSION['debug'] == "on"){
    		print "<span class='debug'>logDailyStatus</span><br>";
    	  }

          include_once './classes/db.class.php';

          if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbConnect: " . __LINE__ . "</span><br>";}
    	
          $conn = NULL;
          $db = NULL;
          $rs = NULL;
          $row = NULL;

          try {
            $conn = new db();
            $conn->fileName = trim($userId);
            $db=$conn->connect();

            $key = getAccessKey($db);
            if ($key != $access)
              throw new Exception("Key is not correct");

 	    $sqlStockList = "INSERT INTO dailystatus (tDate, symbol, shares, cost, currency)";
            $sqlStockList .= " select DATETIME('now','localtime'), symbol, shares, case when ask <= 0 then lastTradePriceOnly else ask end as cost, currency from (";
            $sqlStockList .= "select t.symbol, sum(case when t.activity = 'SELL' THEN t.shares * -1 ELSE t.shares END) AS shares, sum(case when t.activity = 'SELL' THEN shares * cost * -1 ELSE shares * cost END) AS totalCost, t.currency";
            $sqlStockList .= ", (SELECT [value] from stockdata sd where sd.symbol = t.symbol and attribute = 'ask') AS ask";
            $sqlStockList .= ", (SELECT [value] from stockdata sd where sd.symbol = t.symbol and attribute = 'lastTradePriceOnly') AS lastTradePriceOnly";
            $sqlStockList .= " from transactions t";
            $sqlStockList .= " where t.activity in ('BUY','BONUS','SPLIT','SELL') GROUP BY t.symbol, t.currency) WHERE shares != 0 ORDER BY 1";
	    $rsStockList = $db->prepare($sqlStockList);
	    $rsStockList->execute();
	  } catch (Exception $e) {
            if ($_SESSION['debug'] == "on"){print "<span class='debug'>logDailyStatus: " . __LINE__ . ":" . $e->getMessage() . "</span><br>";}
            print $e->getMessage();
            $row = null;
            $rs = null;
            $db = null;
            $conn = null;
	    return false;
	  }

          if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbDisconnect: " . __LINE__ . "</span><br>";}

          $row = null;
          $rs = null;
          $db = null;
          $conn = null;

          return true;
	}

        function getAccessKey($db) {
          $key = NULL;

          # $sql = "SELECT * FROM settings WHERE settingName=:settingName";
          $sql = "SELECT * FROM settings WHERE settingName='accessKey'";
	  # $rs = $db->prepare($sql);
	  # $rs->bindValue(':settingName', $this->settingName);
	  #$rs->execute();
          $rs = $db->query($sql, PDO::FETCH_ASSOC);
	  $row = $rs->fetch();
          $key = $row['settingValue'];

          $row = NULL;
          $rs = NULL;
          $sql = NULL;

          return $key;
        }

	# displays summary bar
	function summaryBar($symbol)
	{
		getData($symbol);

		include_once './classes/tc/stockData.class.php';

		$sData = new stockData();
		$sData->symbol = $symbol;
		$sData->select();

		$currentPrice = $sData->currentPrice2;
		$yearHigh = $sData->yearHigh;
		$yearLow = $sData->yearLow;
		$dividendYield = $sData->yield;
		$dps = $sData->dps;
		$exDividendDate = $sData->xDate;
		$payDate = $sData->pDate;
		$eps = $sData->eps;
		$name = $sData->name;
		$scurrency = $sData->currency;

		$dataSource = "Updates in " . number_format((float)($_SESSION['refreshTime'] - ((time() - $sData->lastUpdated) / 60)), 0, '.', '') . " minutes";

		# connect to the database
		if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbConnect: " . __LINE__ . "</span><br>";}
    	
		include_once './classes/db.class.php';
	
		$conn = new db();
		$conn->fileName = $_SESSION['userId'];
		$db = $conn->connect();

		$sql = "SELECT currency as s FROM transactions where symbol=:symbol GROUP BY currency";
		$rs = $db->prepare($sql);
		$rs->bindValue(':symbol', $symbol);
		$rs->execute();
		$row = $rs->fetch();
		$currency = $row['s'];

		$sql = "SELECT sum(shares) as s FROM transactions where activity IN ('BUY','BONUS','SPLIT') AND symbol=:symbol";
		$rs = $db->prepare($sql);
		$rs->bindValue(':symbol', $symbol);
		$rs->execute();
		$row = $rs->fetch();
		$boughtShares = $row['s'];

		$sql = "SELECT sum(shares) as s FROM transactions where activity='SELL' AND symbol=:symbol";
		$rs = $db->prepare($sql);
		$rs->bindValue(':symbol', $symbol);
		$rs->execute();
		$row = $rs->fetch();
		$soldShares = $row['s'];

		$sql = "SELECT cost, shares FROM transactions where activity IN ('BUY','BONUS','SPLIT') AND symbol=:symbol";
		$rs = $db->prepare($sql);
		$rs->bindValue(':symbol', $symbol);
		$rs->execute();

		$totalSpent = 0;

		while($row = $rs->fetch())
		{
			$sale = $row['shares'] * $row['cost'];
			$totalSpent = $totalSpent + $sale;
		}

		$sql = "SELECT cost, shares FROM transactions where activity='SELL' AND symbol=:symbol";
		$rs = $db->prepare($sql);
		$rs->bindValue(':symbol', $symbol);
		$rs->execute();

		$totalSales = 0;

		while($row = $rs->fetch())
		{
			$sale = $row['shares'] * $row['cost'];
			$totalSales = $totalSales + $sale;
		}

		$sql = "SELECT sum(cost) as s, sum(tax) as t FROM transactions where activity='DIVIDEND' AND symbol=:symbol";
		$rs = $db->prepare($sql);
		$rs->bindValue(':symbol', $symbol);
		$rs->execute();
		$row = $rs->fetch();
		$dividends = $row['s'];
		$dividendstax = $row['t'];

		if($dividends == '')
		{
			$dividends = 0;
		}
		if($dividendstax == '')
		{
			$dividendstax = 0;
		}

		$sql = "SELECT currency, sum(cost) as s FROM transactions where activity='FEE' AND symbol=:symbol GROUP BY currency";
		$rs = $db->prepare($sql);
		$rs->bindValue(':symbol', $symbol);
		$rs->execute();
		$row = $rs->fetch();
		$fees = $row['s'];
		$feecurrency = $row['currency'];

		if (($boughtShares - $soldShares) > 0)
		{
			$pps = ($totalSpent / $boughtShares);
		}
		else
		{
			$pps = 0;
		}

		print "  <div class='spacer'></div>";
		print "  <fieldset>";
		print "    <legend>Summary For " . $name . " (Data " . $dataSource . ")</legend>";
		print "    <table class='data'>";
		print "      <thead>";
		print "        <tr>";
		print "            <th class='data'>";
		print "                Current Position";
		print "            </th>";
		print "            <th class='data'>";
		print "                Overall Position";
		print "            </th>";
		print "            <th class='data'>";
		print "                Stats";
		print "            </th>";
		print "        </tr>";
		print "      </thead>";
		print "      <tbody>";
		print "        <tr>";
		print "            <td class='data' width='33%' style='vertical-align: top;'>";
		print "                <table width='100%'>";
		print "                    <tr>";
		print "                        <td width='50%' class='data'>";
		print "                            Currently Invested";
		print "                        </td>";
		print "                        <td width='50%' class='data'>";

		if(toCash(($totalSpent - $totalSales)) > 0)
		{
		    print formatCashWCurr(($totalSpent - $totalSales), $scurrency);
		}
		else
		{
		    print formatCashWCurr("0.00", $currency);
		}

		print "                        </td>";
		print "                    </tr>";
		print "                    <tr>";
		print "                        <td width='50%' class='data'>";
		print "                            Owned Shares";
		print "                        </td>";
		print "                        <td width='50%' class='data'>";
		print "                            " . ($boughtShares - $soldShares);
		print "                        </td>";
		print "                    </tr>";
		print "                    <tr>";
		print "                        <td class='data'>";
		print "                            Current Price";
		print "                        </td>";
		print "                        <td class='data'>";
		print "                            " . formatCashWCurr($currentPrice, $scurrency);
		print "                        </td>";
		print "                    </tr>";
		print "                    <tr>";
		print "                        <td class='data'>";
		print "                            Current Value";
		print "                        </td>";
		print "                        <td class='data'>";
		print "                            " . formatCashWCurr(($currentPrice * ($boughtShares - $soldShares)), $scurrency);
		print "                        </td>";
		print "                    </tr>";
		print "                </table>";
		print "            <td class='data' width='33%'>";
		print "                <table class='data'>";
		print "                    <tr>";
		print "                        <td class='data' width='50%' align='right'>";
		print "                			   Total Invested";
		print "                        </td>";
		print "                        <td class='data' align='left'>";
		print "                			   " . formatCashWCurr($totalSpent, $scurrency);
		print "                        </td>";
		print "                    </tr>";
		print "                    <tr>";
		print "                        <td class='data' align='right'>";
		print "                            Avg Paid Per Share";
		print "                        </td>";
		print "                        <td class='data' align='left'>";
		print "                            " . formatCashWCurr($pps, $scurrency);
		print "                        </td>";
		print "                    </tr>";
		print "                    <tr>";
		print "                        <td class='data' align='right'>";
		print "                            Total Realized";
		print "                        </td>";
		print "                        <td class='data' align='left'>";
		print "                            " . formatCashWCurr($totalSales, $scurrency);
		print "                        </td>";
		print "                    </tr>";
		print "                    <tr>";
		print "                        <td class='data' align='right'>";
		print "                            Dividends Earned";
		print "                        </td>";
		print "                        <td class='data' align='left'>";
		print "                            " . formatCashWCurr($dividends, $scurrency);
		print "                        </td>";
		print "                    </tr>";
		if ($_SESSION['showTransactionTax'] == 'YES') {
			print "                    <tr>";
			print "                        <td class='data' align='right'>";
			print "                            Dividends Taxes";
			print "                        </td>";
			print "                        <td class='data' align='left'>";
			print "                            " . formatCashWCurr($dividendstax, $scurrency);
			print "                        </td>";
			print "                    </tr>";
		}
		print "                    <tr>";
		print "                        <td class='data' align='right'>";
		print "                            Total Income";
		print "                        </td>";
		print "                        <td class='data' align='left'>";
		print "                            " . formatCashWCurr(($totalSales + $dividends - $dividendstax), $scurrency);
		print "                        </td>";
		print "                    </tr>";
		print "                    <tr>";
		print "                        <td class='data' align='right'>";
		print "                            Total Fees";
		print "                        </td>";
		print "                        <td class='data' align='left'>";
		print "                            " . formatCashWCurr($fees, $feecurrency);
		print "                        </td>";
		print "                    </tr>";
		print "                    <tr>";
		print "                        <td class='data' align='right'>";
		print "                            Rate of Return";
		print "                        </td>";
		print "                        <td class='data' align='left'>";

		if ($totalSpent > 0)
		{
			 print toCash(((((($currentPrice * ($boughtShares - $soldShares)) + $dividends - $dividendstax) - $totalSpent) / $totalSpent) * 100)) . " %";
		}
		else
		{
			print "Rate Of Return Cannot Be Calculated";
		}

		print "                        </td>";
		print "                    </tr>";
		print "                    <tr>";
		print "                        <td class='data' align='right'>";
		print "                            Liquidation Outcome";
		print "                        </td>";
		print "                        <td class='data' align='left'>";

		$standing = toCash((($totalSales + $dividends - $dividendstax) - (($fees)+($totalSpent))+($currentPrice * ($boughtShares - $soldShares))));

		if($standing < 0)
		{
			print "<span class='red'>" . formatCashWCurr($standing, $currency) . "</span>";
		}
		else
		{
			print formatCashWCurr($standing, $currency);
		}

		print "                       </td>";
		print "                    </tr>";
		print "                </table>";
		print "            </td>";
		print "            <td class='data' width='33%' style='vertical-align: top;'>";
		print "                <table width='100%'>";
		print "                    <tr>";
		print "                        <td width='50%' class='data'>";
		print "                			   52 High";
		print "                        </td>";
		print "                        <td width='50%' class='data'>";
		print "                			   " . formatCashWCurr($yearHigh, $scurrency);
		print "                        </td>";
		print "                    </tr>";
		print "                    <tr>";
		print "                        <td width='50%' class='data'>";
		print "                        	   52 Low";
		print "                        </td>";
		print "                        <td width='50%' class='data'>";
		print "                        	   " . formatCashWCurr($yearLow, $scurrency);
		print "                        </td>";
		print "                    </tr>";
		print "                    <tr>";
		print "                        <td class='data'>";
		print "                			   Ex Date";
		print "                        </td>";
		print "                        <td class='data'>";
		print "                			   " . str_replace('"', '', $exDividendDate);
		print "                        </td>";
		print "                    </tr>";
		print "                    <tr>";
		print "                        <td class='data'>";
		print "                        	   Pay Date";
		print "                        </td>";
		print "                        <td class='data'>";
		print "                        	   " . str_replace('"', '', $payDate);
		print "                        </td>";
		print "                    </tr>";
		print "                    <tr>";
		print "                        <td class='data'>";
		print "                			   DPS";
		print "                        </td>";
		print "                        <td class='data'>";
		print "                			   " . $dps;
		print "                        </td>";
		print "                    </tr>";
		print "                    <tr>";
		print "                        <td class='data'>";
		print "                        	   EPS";
		print "                        </td>";
		print "                        <td class='data'>";
		print "                        	   " . $eps;
		print "                        </td>";
		print "                    </tr>";
		print "                    <tr>";
		print "                        <td class='data'>";
		print "                			   Yield";
		print "                        </td>";
		print "                        <td class='data'>";
		print "                			   " . $dividendYield;
		print "                        </td>";
		print "                    </tr>";
		print "                </table>";
		print "            </td>";
		print "        </tr>";
		print "      </tbody>";
		print "    </table>";
		print "  </fieldset>";
        
		if ($_SESSION['debug'] == "on"){
			print "<span class='debug'>dbDisconnect: " . __LINE__ . "</span><br>";
		}
         
		$rs = null;
		$db = null;
		$conn = null;
	}
	
	
	# converts values to cash format (2 decimal places)
	function toCash($value)
	{
        # if ($_SESSION['debug'] == "on"){print "<span class='debug'>toCash($value)</span><br>\n";}

		return number_format((float)$value, 2, '.', ''); // $_SESSION['DecimalPoint'], $_SESSION['ThousandSep']
	}

	# formats a cash value including currency
	function formatCash($value)
	{
        # if ($_SESSION['debug'] == "on"){print "<span class='debug'>formatCash($value)</span><br>\n";}
		return formatCashWCurr($value, $_SESSION['DefaultCurrency']);
	}

	# formats a cash value including currency
	function formatCashWCurr($value, $currency)
	{
        # if ($_SESSION['debug'] == "on"){print "<span class='debug'>formatCashWCurr($value)</span><br>\n";}
		if (strlen($currency) > 1)
		  return number_format((float)$value, 2, $_SESSION['DecimalPoint'], $_SESSION['ThousandSep']) . "&nbsp;" . $currency;
		else
		  return $currency . "&nbsp;" . number_format((float)$value, 2, $_SESSION['DecimalPoint'], $_SESSION['ThousandSep']);
	}
	
	
	function annualDividends($year)
	{
	    include_once './classes/db.class.php';
		
	    $conn = new db();
	    $conn->fileName = $_SESSION['userId'];
	    $db = $conn->connect();

	    $sqlDividend = "SELECT sum(cost) as s FROM transactions where activity='DIVIDEND' AND tDate > '" . $year . "-01-01' AND tDate < '" . $year . "-12-31'";
	    $rsDividend = $db->prepare($sqlDividend);
	    $rsDividend->execute();
	    $rowDividend = $rsDividend->fetch();
	    $dividends = $rowDividend['s'];
		
	    $rowDividend = null;
	    $rsDividend = null;
	    $db = null;
	    $conn = null;	
	
		if ($dividends > 0)
		{
		    return $dividends;
		}
		else
		{
		    return 0;
		}
	}

	function dividendEvents()
	{
	    include_once './classes/db.class.php';
		
	    $conn = new db();
	    $conn->fileName = $_SESSION['userId'];
	    $db = $conn->connect();

	    $sqlDividend = "SELECT group_concat(symbol || ':' || value, ', ') AS DividendData FROM stockData WHERE attribute IN ('exDividendDate','xdividendPayDate') AND value <> '' AND value BETWEEN date('now','start of month','-15 day') AND date('now','start of month','+1 month','-1 day') ORDER BY value asc";
	    $rsDividend = $db->prepare($sqlDividend);
	    $rsDividend->execute();
	    $rowDividend = $rsDividend->fetch();
	    if (strlen(trim($rowDividend['DividendData'])) > 0) {
		print "<div class='spacer'></div>\n";
		print "<fieldset>";
		print "<legend>Dividend announcements</legend>";
		message("success", trim($rowDividend['DividendData']));
		print "</fieldset>\n";
	    }
	}

?>
