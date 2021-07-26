<?php
    /**
     * form to delete a stock and all related data
     */
    class formDeleteStock
    {
        /**
         * the action value to use in the form
         * @var string
         */
        public $action;
        
        /**
         * holds errors found during check
         * @var string
         */
        public $errors;
        
        
        
        /**
         * displays the form
         */
        function display()
        {
            if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbConnect: index.php " . __LINE__ . "</span><br>";}
        	
            # get a list of stocks
            include_once './classes/db.class.php';
            
            $conn = new db();
            $conn->fileName = $_SESSION['userId'];
            $db=$conn->connect();
    
    		$sql = "SELECT * FROM stocks  ORDER BY symbol";
    		$rs = $db->prepare($sql);
    		$rs->execute();
    
    		# build the delete stock combo box
    		print "<h3>Delete Stock Data</h3>\n";
    		print "<form id='formDeleteStock' method='post' action='" . htmlentities($_SERVER['PHP_SELF']) . "'>\n";
    		print "    <select id='formDeleteStockSymbol' name='symbol' style='width: 130px;'>\n";
    		print "        <option selected value=''>Select Symbol</option>\n";
    
    		while ($row = $rs->fetch())
    		{
    			print "<option value='" . $row['symbol'] . "'>" . $row['symbol'] . "</option>\n";
    		}
    
    		print "    </select>\n";
    		print "    <input type='hidden' name='action' value='" . $this->action . "'>\n";
    		print "    <input type='submit' value='Delete' style='width: 100px;'>\n";
    		print "</form>\n";
    		
            if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbDisconnect: index.php " . __LINE__ . "</span><br>";}
            
    		$row = null;
    		$rs = null;
    		$db = null;
    		$conn = null;
        }
        
        
        # validates the form
        function check()
        {
        	# symbol
        	if(isset($_REQUEST['symbol']))
			{
				# if symbol is not alpha/numeric only...
				# if(!ctype_alnum(trim($_REQUEST['symbol'])))
				# {
				#	# invalid characters error
				#	$this->errors .= "Invalid characters in stock symbol<br>";
				# }
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
        function deleteStock()
        {
        	$this->check();
        	
        	# if there were no errors...
        	if($this->errors = '')
        	{
        		# delete the stock
        		include_once './classes/tc/stocks.class.php';
        		
        		$stock = new stocks();
        		$stock->symbol = $_REQUEST['symbol'];
        		$stock->delete();
        		
        		if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbConnect: " . __LINE__ . "</span><br>";}
        		
        		# delete all transactions for the stock
        		include_once './classes/db.class.php';
        		
        		$conn = new db();
        		$conn->fileName = $_SESSION['userId'];
        		$db=$conn->connect();
        		
        		$sql = "DELETE FROM transactions WHERE symbol=:symbol";
        		$stmt = $db->prepare($sql);
        		$stmt->bindValue(':symbol', trim(strtoupper($_REQUEST['symbol'])));
        		$stmt->execute();
        		
        		if ($_SESSION['debug'] == "on"){
        			print "<span class='debug'>dbDisconnect: " . __LINE__ . "</span><br>";
        		}
        			 
        		$stmt = null;
        		$db = null;
        		$conn = null;
        		
        		# display a success message
        		message("success", "Stock symbol (" . strtoupper($_REQUEST['symbol']) . ") removed & all data purged");
        	}
        }
    }
?>
