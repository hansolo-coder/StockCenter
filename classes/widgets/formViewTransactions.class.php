<?php
    /**
     * form for viewing a stock transaction log
     */
    class formViewTransactions
    {
        /**
         * @var value of form action field
         */
        public $action;
        
        
        
    	/**
    	 * displays the form
    	 */
    	function display()
    	{
            if ($_SESSION['debug'] == "on"){print "<span class='debug'>stockActivityForm($this->action)</span><br>\n";}
    
    
            include_once './classes/db.class.php';
    
            if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbConnect: " . __LINE__ . "</span><br>";}
            
            $conn = new db();
            $conn->fileName = $_SESSION['userId'];
            $db=$conn->connect();
    
    		$sql = "SELECT * FROM stocks ORDER BY symbol";
    		$rs = $db->prepare($sql);
    		$rs->execute();
    
    		# view stock combo box
    		print "<h3>Manage Stock Activity</h3>\n";
    		print "<form method='post' action='" . htmlentities($_SERVER['PHP_SELF']) . "'>\n";
    		print "    <select name='symbol' style='width: 130px;'>\n";
    		print "        <option selected value=''>Select Symbol</option>\n";
    
    		while ($row = $rs->fetch())
    		{
    			print "<option value='" . $row['symbol'] . "'>" . $row['symbol'] . "</option>\n";
    		}
    
    		print "    </select>\n";
    		print "    <input type='hidden' name='action' value='" . $this->action . "'>\n";
    		print "    <input type='submit' value='View' style='width: 100px;'>\n";
    		print "</form>\n";
    		
    	    if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbDisconnect: " . __LINE__ . "</span><br>";}
    		
    	    $row = NULL;
    		$rs = NULL;
    		$db = null;
    		$conn = null;
        }
    }
?>