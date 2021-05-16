<?php
	/**
	 * form for showing charts
	 */
	class formCharts
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
			if ($_SESSION['debug'] == "on"){print "<span class='debug'>showChart($this->action)</span><br>\n";}

			if ($this->action == 'holdingsValueChart') {
				$this->holdingsValueChart();
			} else if ($this->action == 'sharesOwnedChart') {
				$this->sharesOwnedChart();
			} else {
				$this->error = 'Unknown action: ' . $this->action;
			}
		}

		
		/**
		 * shares value chart
		 */
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


		/**
		 * shares owned chart
		 */
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
		
	}
?>
