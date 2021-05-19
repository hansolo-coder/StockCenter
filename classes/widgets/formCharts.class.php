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
		function display($db)
		{
			if ($_SESSION['debug'] == "on"){print "<span class='debug'>showChart($this->action)</span><br>\n";}

			if ($this->action == 'holdingsValueChart') {
				$this->holdingsValueChart($db);
			} else if ($this->action == 'sharesOwnedChart') {
				$this->sharesOwnedChart($db);
			} else if ($this->action == 'marketChart') {
				$this->marketChart($db);
			} else {
				$this->error = 'Unknown action: ' . $this->action;
			}
		}

		function displayAll() {
			include_once './classes/db.class.php';
	
			$conn = new db();
			$conn->fileName = $_SESSION['userId'];
			$db = $conn->connect();

			print "<div>\n";
				$this->holdingsValueChart($db);
			print "</div>\n";
			print "<div>\n";
				$this->sharesOwnedChart($db);
			print "</div>\n";
			print "<div>\n";
				$this->marketChart($db);
			print "</div>\n";

			$db = null;
			$conn = null;
		}

		function executeScriptFrontpage() {
			print "<script>\n";
			print "    function start()\n";
			print "    {\n";
			print "        showSharesOwnedChart();\n";
			print "        showHoldingsValueChart();\n";
			print "    }\n";
			print "    window.onload = start();\n";
			print "</script>\n";
		}

		function executeScriptAll() {
			print "<script>\n";
			print "    function start()\n";
			print "    {\n";
			print "        showSharesOwnedChart();\n";
			print "        showHoldingsValueChart();\n";
			print "        showMarketValueChart();\n";
			print "    }\n";
			print "    window.onload = start();\n";
			print "</script>\n";
		}
		
		/**
		 * shares value chart
		 */
		function holdingsValueChart($db) {
			if ($_SESSION['debug'] == "on"){print "<span class='debug'>holdingsValueChart: " . __LINE__ . "</span><br>";}

			$sqlStocks = "SELECT symbol FROM stocks order by symbol";
			$rsStocks = $db->prepare($sqlStocks);
			$rsStocks->execute();
			$stocks = $rsStocks->fetchAll();

			$rsStocks = null;
        
			$labels = '[';
			$data = '[';

			foreach($stocks as $stock) {
				include_once './classes/tc/stockData.class.php';

				$sData = new stockData();
				$sData->symbol = $stock['symbol'];
				$sData->select();

				$currentPrice = $sData->currentPrice2;

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

				if($ownedShares > 0) {
					$labels .= '"' . $stock['symbol'] . '",';
					$data .= toCash($currentValue) . ',';
				}

				$rs = null;
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
			print "  <legend>Portfolio Value</legend>";
			print "  <div style='width:100%; margins: auto;'>";
			print "	   <div>";
			print "		   <canvas id='value' height='50' width='100%'></canvas>";
			print "	   </div>";
			print "  </div>";
			print "  <script>";
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
			print "  </script>";
			print "</fieldset>";
		}


		/**
		 * shares owned chart
		 */
		function sharesOwnedChart($db) {
			if ($_SESSION['debug'] == "on"){print "<span class='debug'>sharesOwnedChart: " . __LINE__ . "</span><br>";}
    	
			$sqlStocks = "SELECT symbol FROM stocks order by symbol";
			$rsStocks = $db->prepare($sqlStocks);
			$rsStocks->execute();
			$stocks = $rsStocks->fetchAll();
        
			$rsStocks = null;
        
			$labels = '[';
			$data = '[';

			foreach($stocks as $stock) {
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

				if($ownedShares > 0) {
					$labels .= '"' . $stock['symbol'] . '",';
					$data .= $ownedShares . ',';
				}
            
				$rsStocks = null;
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
			print "  <legend>Share Allocation</legend>";
			print "  <div style='width:100%; margins: auto;'>";
			print "	   <div>";
			print "		   <canvas id='shares' height='50' width='100%'></canvas>";
			print "	   </div>";
			print "  </div>";
			print "  <script>";
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
			print "  </script>";
			print "</fieldset>";
		}

		/*
	 	* Show value split per market
		 */
		function marketChart($db) {
/*
			$sql = "SELECT t.symbol," .
			" SUM(CASE WHEN t.activity IN ('BUY', 'BONUS', 'SPLIT') THEN t.shares ELSE 0 END) AS boughtshares," .
			" SUM(CASE WHEN t.activity IN ('SELL', 'MOVE') THEN t.shares ELSE 0 END) AS soldshares, COALESCE(sd.value, 'Unknown') AS market" .
			" FROM transactions t LEFT OUTER JOIN stockData sd ON t.symbol = sd.symbol AND sd.attribute = 'market'" .
			" GROUP BY t.symbol ORDER BY t.symbol";
*/
			$sql = "SELECT market, SUM((boughtshares - soldshares) * price) AS marketValue, currency as value FROM (" .
			"SELECT innerdata.*, COALESCE(sd.value, 'Unknown') AS market,COALESCE(sdp.value, sdb.value, 0) AS price,sdp.value, sdb.value FROM (" .
			"SELECT t.symbol,SUM(CASE WHEN t.activity IN ('BUY', 'BONUS', 'SPLIT') THEN t.shares ELSE 0 END) AS boughtshares" .
			",SUM(CASE WHEN t.activity IN ('SELL', 'MOVE') THEN t.shares ELSE 0 END) AS soldshares, t.currency" .
			" FROM transactions t" .
			" GROUP BY t.symbol, t.currency" .
			") innerdata" .
			" LEFT OUTER JOIN stockData sd ON innerdata.symbol = sd.symbol AND sd.attribute = 'market'" .
			" LEFT OUTER JOIN stockData sdp ON innerdata.symbol = sdp.symbol AND sdp.attribute = 'ask' and sdp.value <> 0" .
			" LEFT OUTER JOIN stockData sdb ON innerdata.symbol = sdb.symbol AND sdb.attribute = 'bid' and sdb.value <> 0" .
			") innerdata GROUP BY market ORDER BY 1";

			$rsStocks = $db->prepare($sql);
			$rsStocks->execute();
			$stocks = $rsStocks->fetchAll();

			$rsStocks = null;
         
			$data = '[';

			#               Red        Green      Yellow     Grey       DarkGrey
			$fcolor = array("#F7464A", "#46BFBD", "#FDB45C", "#949FB1", "#4D5360");
			$hcolor = array("#FF5A5E", "#5AD3D1", "#FFC870", "#A8B3C5", "#616774");

			$idx = 0;
			foreach($stocks as $stock) {
		                $data .= '{value: ' . $stock['marketValue'] . ', color:"' . $fcolor[$idx] . '", highlight: "' . $hcolor[$idx] . '", label: "' . $stock['market'] . '" }' . ',';
				if (++$idx >= count($fcolor))
					$idx = 0;
			}

			# trim off the last comma
			$data = rtrim($data, ",");

			# cap off the dataset
			$data .= "]";

			# prepare the chart
			print "<script src='javascript/chart/Chart.js'></script>";
			print "<fieldset>";
			print "  <legend>Market Value</legend>";
			print "  <div style='width:100%; margins: auto;'>";
			print "	   <div>";
			print "		   <canvas id='marketCanvas' height='240' width='auto'></canvas>";
			print "	   </div>";
			print "  </div>";
			print "  <script>";
			?>
			var pieData = <?php print $data; ?>;

			function showMarketValueChart(){
				var ctx = document.getElementById("marketCanvas").getContext("2d");
				window.myPie = new Chart(ctx).Pie(pieData);
			}
			<?php
			print "  </script>";
			print "</fieldset>";
		}		
	}
?>
