<?php
// https://tryshchenko.com/archives/225/
// https://www.sitepoint.com/introduction-chart-js-2-0-six-examples/
// https://www.stanleyulili.com/javascript/beginner-guide-to-chartjs/
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
		 * parameter used for some charts
		 * @var string
		 */
		public $parm1;

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
			} else if ($this->action == 'stockPriceHistory') {
				$this->displayedCharts |= 64;
				$this->symbolPriceHistoryChart($db, $this->parm1);
			} else if ($this->action == 'dividendEarnings') {
				$this->displayedCharts |= 128;
				$this->dividendEarningsChart($db, $this->parm1);
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
			if (($this->displayedCharts & 64) == 64)
				print "        showStockPriceHistoryValueChart();\n";
			if (($this->displayedCharts & 128) == 128)
				print "        showDividendEarningsChart();\n";
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
			print "<script src='javascript/chart/dist/Chart.bundle.js'></script>";
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
					backgroundColor : "#eaeaea",
					borderColor : "#979797",
					borderWidth : 0.5,
					pointColor : "rgba(220,220,220,1)",
					pointStrokeColor : "#fff",
					pointHighlightFill : "#fff",
					pointHighlightStroke : "rgba(220,220,220,1)",
  barThickness: 3,
  barPercentage: 0.5,
					data : <?php print $data; ?>
					}
        	        	]
			}

			function showHoldingsValueChart(){
				var valuectx = document.getElementById("value").getContext("2d");
				window.myValue = new Chart(valuectx, {
			                type: 'bar',
			                data: valueChartData, 
			                options: {
						legend: { display: false },
						XmaintainAspectRatio: false,
						title: {
						    display: false,
						    text: 'Custom Chart Title'
						},
						responsive: true, 
						scales: {
        						x: {
						          // beginAtZero: true,
							  max: 50
						        },
        						y	: {
						          // beginAtZero: true,
							  max: 50
						        },
							xAxes: [{
								barPercentage: 0.5,
								XXposition: "top",
								ticks: {
									autoSkip: false,
								}
							}],
							yAxes: [{
								ticks: {
									beginAtZero: false,
								}
							}]
						}
					}
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
			print "<script src='javascript/chart/dist/Chart.bundle.js'></script>";
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
					backgroundColor : "#eaeaea",
					borderColor : "#979797",
					borderWidth : 0.5,
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
				window.myShare = new Chart(sharectx, {
					type: 'bar',
			                data: shareChartData, 
					options: {
						legend: { display: false },
						responsive: true,
						scales: {
							xAxes: [{
								barPercentage: 0.5,
								XXposition: "top",
								ticks: {
									autoSkip: false,
								}
							}]
						}

					}
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
         
			$labels = '[';
			$data = '[';
			$backgroundColor = '[';

			$idx = 0;
			foreach($stocks as $stock) {
				$labels .= '"' . $stock['market'] . '",';
		                $data .= $stock['marketValue'] . ',';
				$backgroundColor .= '"' . $this->fcolor[$idx] . '"' . ',';
				if (++$idx >= count($this->fcolor))
					$idx = 0;
			}

			# trim off the last comma
			$labels = rtrim($labels, ",");
			$data = rtrim($data, ",");
			$backgroundColor = rtrim($backgroundColor, ",");

			# cap off the dataset
			$labels .= "]";
			$data .= "]";
			$backgroundColor .= "]";

			# prepare the chart
			print "<script src='javascript/chart/dist/Chart.bundle.js'></script>";
			print "<fieldset>";
			print "  <legend>" . $headerText . "</legend>";
			print "  <div style='width:100%; margins: auto;'>";
			print "	   <div>";
			print "		   <canvas id='" . $stockdataAttribute . "Canvas' height='240' width='auto'></canvas>";
			print "	   </div>";
			print "  </div>";
			print "  <script>";
			?>
			var <?php print $stockdataAttribute; ?>PieData = {
				labels : <?php print $labels; ?>,
				datasets : [
					{
					label: '<?php print $stockdataAttribute; ?>',
					backgroundColor : <?php print $backgroundColor; ?>,
					data : <?php print $data; ?>
					}
				]
			}

			function show<?php print $stockdataAttribute; ?>ValueChart(){
				var ctx = document.getElementById("<?php print $stockdataAttribute; ?>Canvas").getContext("2d");
				window.my<?php print $stockdataAttribute; ?>Pie = new Chart(ctx, {
					type: 'pie',
					data: <?php print $stockdataAttribute; ?>PieData
				});
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
			print "<script src='javascript/chart/dist/Chart.bundle.js'></script>";
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
						backgroundColor: "rgba(220,220,220,0.2)",
						borderColor: "rgba(220,220,220,1)",
						pointColor: "rgba(220,220,220,1)",
						pointStrokeColor: "#fff",
						pointHighlightFill: "#fff",
						pointHighlightStroke: "rgba(220,220,220,1)",
						data: <?php print $data1; ?>
					},
					{
						label: "<?php print $stockdataAttribute2; ?>",
						backgroundColor: "rgba(151,187,205,0.2)",
						borderColor: "rgba(151,187,205,1)",
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
				window.my<?php print $stockdataAttribute1; ?>Radar = new Chart(ctx, {
					type: 'radar',
					data: <?php print $stockdataAttribute1; ?>RadarChartData,
					options: {
						legend: {
						   position: 'top',
						},
						title: {
						    display: true,
						    text: 'Chart.js Radar Chart'
						},
						scale: {
						    reverse: false,
						    ticks: {
							beginAtZero: true
						    }
						}
					}
				});
			}
			<?php
			print "  </script>";
			print "</fieldset>";
		}

		/*
	 	* Show value split per selected stockData attribute
		 */
		function symbolPriceHistoryChart($db, $symbol) {
			$sql = "select SUBSTR(tDate, 1, 10) AS tDate, shares, cost, currency from dailystatus where symbol = :symbol ORDER BY tDate";

			$rsObs = $db->prepare($sql);
			$rsObs->bindValue(':symbol', $symbol);
			$rsObs->execute();
			$observations = $rsObs->fetchAll();

			$rsObs = null;
         
			$labels = '[';
			$data = '[';

			$idx = 0;
			foreach($observations as $observation) {
				$labels .= '"' . $observation['tDate'] . '",';
				$data .= $observation['cost'] . ',';
			}

			# trim off the last comma
			$labels = rtrim($labels, ",");
			$data = rtrim($data, ",");

			# cap off the dataset
			$labels .= "]";
			$data .= "]";

			# prepare the chart
			print "<script src='javascript/chart/dist/Chart.bundle.js'></script>";
			print "<fieldset>";
			print "  <legend>Stock price history</legend>";
			print "  <div style='width:100%; margins: auto;'>";
			print "	   <div>";
			print "		   <canvas id='stockPriceHistoryCanvas' height='120' width='auto'></canvas>";
			print "	   </div>";
			print "  </div>";
			print "  <script>";
			?>
			var stockHistLineChartData = {
				labels : <?php print $labels; ?>,
				datasets : [
					{
						label: "Stock History Price",
						backgroundColor : "rgba(220,220,220,0.2)",
						borderColor : "rgba(220,220,220,1)",
						pointColor : "rgba(220,220,220,1)",
						pointStrokeColor : "#fff",
						pointHighlightFill : "#fff",
						pointHighlightStroke : "rgba(220,220,220,1)",
						data : <?php print $data; ?>
					}
				]
			}

			function showStockPriceHistoryValueChart(){
				var ctx = document.getElementById("stockPriceHistoryCanvas").getContext("2d");
				window.myLine = new Chart(ctx, {
					type: 'line',
					data: stockHistLineChartData,
					options : {
						legend: { display: false },
						responsive: true,
						scales: {
							xAxes: [{
								XXposition: "top",
								ticks: {
									autoSkip: true,
								}
							}]
						}
					}
				});
			}
			<?php
			print "  </script>";
			print "</fieldset>";
		}

		/*
		 * Dividend earnings chart
		 */
		function dividendEarningsChart($db, $symbol) {
			# get the dividend data
			$sql = "SELECT * FROM (SELECT t.tDate, t.symbol, t.activity, t.shares, case when t.currency = a.accountcurrency then t.cost else t.cost * t.exchangerate end as cost, t.currency, case when t.currency = a.accountcurrency then t.tax else t.tax * t.exchangerate end as tax, t.exchangerate, a.accountcurrency FROM transactions t inner join accounts a on a.accountid = t.accountid where t.activity='DIVIDEND' AND t.symbol=:symbol ORDER BY t.tdate DESC LIMIT 10) ORDER BY tdate;";
			$rs = $db->prepare($sql);
			$rs->bindValue(':symbol', $symbol);
			$rs->execute();
			$rows = $rs->fetchall();

			$labels = '[';
			$data = '[';

			foreach($rows as $row) {
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
			print "<script src='javascript/chart/dist/Chart.bundle.js'></script>";
			print "<fieldset>";
			print "<legend>Last 10 Dividend Payments</legend>";
			print "<div style='width:100%; margins: auto;'>";
			print "	   <div>";
			print "		   <canvas id='dividendEarningsCanvas' height='15' width='100%'></canvas>";
			print "	   </div>";
			print "</div>";
			print "<script>";
			?>
			var dividendLineChartData = {
				labels : <?php print $labels; ?>,
				datasets : [
					{
						label: "Dividend Payments",
						backgroundColor : "rgba(220,220,220,0.2)",
						borderColor : "rgba(220,220,220,1)",
						pointColor : "rgba(220,220,220,1)",
						pointStrokeColor : "#fff",
						pointHighlightFill : "#fff",
						pointHighlightStroke : "rgba(220,220,220,1)",
						data : <?php print $data; ?>
					}
                		]
			}
			function showDividendEarningsChart(){
				var ctx = document.getElementById("dividendEarningsCanvas").getContext("2d");
				window.myLine = new Chart(ctx, {
					type: 'line',
					data: dividendLineChartData,
					options: {
						legend: { display: false },
						responsive: true
					}
				});
			}
			<?php
			print "  </script>";
			print "</fieldset>";
		} // dividendEarningsChart
	}
?>
