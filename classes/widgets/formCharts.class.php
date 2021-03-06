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
		
			#               Red        Cyan       Yellow     Grey       DarkGrey   Green      Blue       Yellow     Pink
		private $fcolor = array("#F7464A", "#46BFBD", "#FDB45C", "#949FB1", "#4D5360", "#33ff33", "#4d4dff", "#ffff00", "#ff66ff");
		private $hcolor = array("#FF5A5E", "#5AD3D1", "#FFC870", "#A8B3C5", "#616774", "#80ff80", "#8080ff", "#ffff80", "#ffb3ff");

		private $displayedCharts = 0;
		
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
				$this->displayedCharts |= 1;
				$this->holdingsValueChart($db);
			} else if ($this->action == 'sharesOwnedChart') {
				$this->displayedCharts |= 2;
				$this->sharesOwnedChart($db);
			} else if ($this->action == 'marketChart') {
				$this->displayedCharts |= 4;
				$this->stockdataChart($db, 'market', 'Market Value');
			} else if ($this->action == 'sectorChart') {
				$this->displayedCharts |= 8;
				$this->stockdataChart($db, 'sector', 'Sector Value');
			} else if ($this->action == 'industryChart') {
				$this->displayedCharts |= 16;
				$this->stockdataChart($db, 'industry', 'Industry Value');
			} else if ($this->action == 'stockdataRadarChart') {
				$this->displayedCharts |= 32;
				$this->stockdataRadarChart($db, 'dps', 'earningsPerShare', 'Stock Data');
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
			$this->displayedCharts |= 1;
			$this->holdingsValueChart($db);
			print "</div>\n";
			print "<div>\n";
			$this->displayedCharts |= 2;
			$this->sharesOwnedChart($db);
			print "</div>\n";
			print "<div>\n";
			$this->displayedCharts |= 4;
			$this->stockdataChart($db, 'market', 'Market Value');
			print "</div>\n";
			print "<div>\n";
			$this->displayedCharts |= 8;
			$this->stockdataChart($db, 'sector', 'Sector Value');
			print "</div>\n";
			print "<div>\n";
			$this->displayedCharts |= 16;
			$this->stockdataChart($db, 'industry', 'Industry Value');
			print "</div>\n";
			print "<div>\n";
			$this->displayedCharts |= 32;
			#$this->stockdataRadarChart($db, 'dps', 'earningsPerShare', 'Stock Data');
			$this->stockdataRadarChart($db, 'dividendYield', 'earningsPerShare', 'Stock Data');
			print "</div>\n";

			$db = null;
			$conn = null;
		}

		function printExecuteScripts() {
			print "<script>\n";
			print "    function start()\n";
			print "    {\n";
			if (($this->displayedCharts & 1) == 1)
				print "        showHoldingsValueChart();\n";
			if (($this->displayedCharts & 2) == 2)
				print "        showSharesOwnedChart();\n";
			if (($this->displayedCharts & 4) == 4)
				print "        showmarketValueChart();\n";
			if (($this->displayedCharts & 8) == 8)
				print "        showsectorValueChart();\n";
			if (($this->displayedCharts & 16) == 16)
				print "        showindustryValueChart();\n";
			if (($this->displayedCharts & 32) == 32)
				#print "        showdpsValueChart();\n";
				print "        showdividendYieldValueChart();\n";
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
	 	* Show value split per selected stockData attribute
		 */
		function stockdataChart($db, $stockdataAttribute, $headerText) {
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
			" LEFT OUTER JOIN stockData sd ON innerdata.symbol = sd.symbol AND sd.attribute = :attribute" .
			" LEFT OUTER JOIN stockData sdp ON innerdata.symbol = sdp.symbol AND sdp.attribute = 'ask' and sdp.value <> 0" .
			" LEFT OUTER JOIN stockData sdb ON innerdata.symbol = sdb.symbol AND sdb.attribute = 'bid' and sdb.value <> 0" .
			") innerdata GROUP BY market ORDER BY 1";

			$rsStocks = $db->prepare($sql);
			$rsStocks->bindValue(':attribute', $stockdataAttribute);
			$rsStocks->execute();
			$stocks = $rsStocks->fetchAll();

			$rsStocks = null;
         
			$data = '[';

			$idx = 0;
			foreach($stocks as $stock) {
		                $data .= '{value: ' . $stock['marketValue'] . ', color:"' . $this->fcolor[$idx] . '", highlight: "' . $this->hcolor[$idx] . '", label: "' . $stock['market'] . '" }' . ',';
				if (++$idx >= count($this->fcolor))
					$idx = 0;
			}

			# trim off the last comma
			$data = rtrim($data, ",");

			# cap off the dataset
			$data .= "]";

			# prepare the chart
			print "<script src='javascript/chart/Chart.js'></script>";
			print "<fieldset>";
			print "  <legend>" . $headerText . "</legend>";
			print "  <div style='width:100%; margins: auto;'>";
			print "	   <div>";
			print "		   <canvas id='" . $stockdataAttribute . "Canvas' height='240' width='auto'></canvas>";
			print "	   </div>";
			print "  </div>";
			print "  <script>";
			?>
			var <?php print $stockdataAttribute; ?>PieData = <?php print $data; ?>;

			function show<?php print $stockdataAttribute; ?>ValueChart(){
				var ctx = document.getElementById("<?php print $stockdataAttribute; ?>Canvas").getContext("2d");
				window.my<?php print $stockdataAttribute; ?>Pie = new Chart(ctx).Pie(<?php print $stockdataAttribute; ?>PieData);
			}
			<?php
			print "  </script>";
			print "</fieldset>";
		}

		/*
	 	* Show selected stockData attribute values for stocks
		 */
		function stockdataRadarChart($db, $stockdataAttribute1, $stockdataAttribute2, $headerText) {
			$sql = "SELECT s.symbol, COALESCE(NULLIF(sd1.value, ''), 0) AS value1, COALESCE(NULLIF(sd2.value, ''), 0) AS value2, sdc.value as currency" .
			" FROM stocks s" .
			" LEFT OUTER JOIN stockData sd1 ON s.symbol = sd1.symbol AND sd1.attribute = :attribute1" .
			" LEFT OUTER JOIN stockData sd2 ON s.symbol = sd2.symbol AND sd2.attribute = :attribute2" .
			" LEFT OUTER JOIN stockData sdc ON s.symbol = sdc.symbol AND sdc.attribute = 'currency'" .
			" ORDER BY 1";

			$rsStocks = $db->prepare($sql);
			$rsStocks->bindValue(':attribute1', $stockdataAttribute1);
			$rsStocks->bindValue(':attribute2', $stockdataAttribute2);
			$rsStocks->execute();
			$stocks = $rsStocks->fetchAll();

			$rsStocks = null;
         
			$labels = '[';
			$data1 = '[';
			$data2 = '[';

			foreach($stocks as $stock) {
				$labels .= '"' . $stock['symbol'] . '",';
				$data1 .= $stock['value1'] . ',';
				$data2 .= $stock['value2'] . ',';
			}

			# trim off the last comma
			$labels = rtrim($labels, ",");
			$data1 = rtrim($data1, ",");
			$data2 = rtrim($data2, ",");

			# cap off the dataset
			$labels .= "]";
			$data1 .= "]";
			$data2 .= "]";

			# prepare the chart
			print "<script src='javascript/chart/Chart.js'></script>";
			print "<fieldset>";
			print "  <legend>" . $headerText . "</legend>";
			print "  <div style='width:100%; margins: auto;'>";
			print "	   <div>";
			print "		   <canvas id='" . $stockdataAttribute1 . "Canvas' height='240' width='auto'></canvas>";
			print "	   </div>";
			print "  </div>";
			print "  <script>";
			?>
			var <?php print $stockdataAttribute1; ?>RadarChartData = {
				labels: <?php print $labels; ?>,
				datasets: [
					{
						label: "<?php print $stockdataAttribute1; ?>",
						fillColor: "rgba(220,220,220,0.2)",
						strokeColor: "rgba(220,220,220,1)",
						pointColor: "rgba(220,220,220,1)",
						pointStrokeColor: "#fff",
						pointHighlightFill: "#fff",
						pointHighlightStroke: "rgba(220,220,220,1)",
						data: <?php print $data1; ?>
					},
					{
						label: "<?php print $stockdataAttribute2; ?>",
						fillColor: "rgba(151,187,205,0.2)",
						strokeColor: "rgba(151,187,205,1)",
						pointColor: "rgba(151,187,205,1)",
						pointStrokeColor: "#fff",
						pointHighlightFill: "#fff",
						pointHighlightStroke: "rgba(151,187,205,1)",
						data: <?php print $data2; ?>
					}
				]
			};

			function show<?php print $stockdataAttribute1; ?>ValueChart(){
				var ctx = document.getElementById("<?php print $stockdataAttribute1; ?>Canvas").getContext("2d");
				window.my<?php print $stockdataAttribute1; ?>Radar = new Chart(ctx).Radar(<?php print $stockdataAttribute1; ?>RadarChartData, {
					responsive: true
				});
			}
			<?php
			print "  </script>";
			print "</fieldset>";
		}
	}
?>
