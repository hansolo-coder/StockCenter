<?php
    /**
     * manages the stocks
     */
    class listStocks
    {
        /**
         * display the stocks
         */
        function show()
        {
            if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbConnect: listStocks.php " . __LINE__ . "</span><br>";}
    
            include_once './classes/db.class.php';
    
            $conn = new db();
            $conn->fileName = $_SESSION['userId'];
            $db=$conn->connect();
    
            // get accounts
            $sql = "SELECT * FROM stocks ORDER BY name ASC";
            $rs = $db->prepare($sql);
            $rs->execute();
            $rows = $rs->fetchAll();
    
            if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbDisconnect: listStocks.php " . __LINE__ . "</span><br>";}
                
            $rs = NULL;
            $db = NULL;
            $conn = NULL;
    
            include_once './classes/pageHeader.class.php';
            $header = new pageHeader();
            $header->display();
                
            print "<div class='spacer'></div>\n";
    
            print "<fieldset>\n";
            print "    <legend>\n";
            print "        Stocks\n";
            print "    </legend>\n";
            print "<table class='data'>\n";
            print "  <thead>\n";
            print "    <tr>\n";
            print "        <th class='data'>\n";
            print "            Symbol\n";
            print "        </th>\n";
            print "        <th class='data'>\n";
            print "            ISIN\n";
            print "        </th>\n";
            print "        <th class='data'>\n";
            print "            Name\n";
            print "        </th>\n";
            print "        <th class='data'>\n";
            print "            SkipLookup\n";
            print "        </th>\n";
            print "        <th class='data'>\n";
            print "            &nbsp;\n";
            print "        </th>\n";
            print "        <th class='data'>\n";
            print "            &nbsp;\n";
            print "        </th>\n";
            print "    </tr>\n";
            print "  </thead>\n";
            print "  <tbody>\n";
            print "    <form action='" . htmlentities($_SERVER['PHP_SELF']) . "?action=addStock2' method='post'>\n";
            print "    <tr>\n";
            print "        <td class='data'>\n";
            print "            <input type='text' name='symbol' class='date'>\n";
            print "        </td>\n";
            print "        <td class='data'>\n";
            print "            <input type='text' name='ISIN' class='medium'>\n";
            print "        </td>\n";
            print "        <td class='data'>\n";
            print "            <input type='text' name='name' class='large'>\n";
            print "        </td>\n";
            print "        <td class='data'>\n";
            print "            <input type='text' name='skipLookup' class='mini' maxlength='1'>\n";
            print "        </td>\n";
            print "        <td class='data'>\n";
            print "            -\n";
            print "        </td>";
            print "        <td class='data'>\n";
            print "            -\n";
            print "        </td>";
            print "        <td class='data'>\n";
            print "            <input type='submit' value='Save'>\n";
            print "        </td>\n";
            print "    </tr>\n";
            print "    </form>\n";
    
            foreach($rows as $row)
            {
                print "    <tr style='background-color: #ABD9FF;'>\n";
                print "        <td class='data'>\n";
                print "            <a href='index.php?action=activityLog&symbol=" . $row['symbol'] ."'>" . $row['symbol'] ."</a>";
                print "        </td>\n";
                print "        <td class='data'>\n";
                print "            " . $row['ISIN'];
                print "        </td>\n";
                print "        <td class='data'>\n";
                print "            " . $row['name'];
                print "        </td>\n";
                print "        <td class='data'>\n";
                print "            " . (string)$row['SkipLookup'];
                print "        </td>\n";
                print "        <td class='data'>\n";
                print "            <a class='delete' href='index.php?action=deleteStock2&id=" . $row['symbolId'] . "'>Delete</a>\n";
                print "        </td>\n";
                print "        <td class='data'>\n";
                print "            <a class='delete' href='index.php?action=updateStock2&id=" . $row['symbolId'] . "'>Update</a>\n";
                print "        </td>\n";
                print "    </tr>\n";
            } // foreach
    
            print "  </tbody>\n";
            print "</table>\n";
            print "</fieldset>\n";
        } // show
        
        
        
    /**
     * adds a new stock
     */
    function addStock()
    {
        if ($_SESSION['debug'] == "on"){print "<span class='debug'>listStocks-> addStock()</span><br>";}
    
	$inError = false;
        if(!(isset($_REQUEST['symbol']) and trim($_REQUEST['symbol']) != ''))
		{
            message("error", "Symbol must be provided");
			$inError = true;
		} elseif (!(isset($_REQUEST['name']) and trim($_REQUEST['name']) != ''))
		{
            message("error", "Name must be provided");
			$inError = true;
		}
        if($inError)
		{
            $log = new listStocks();
            $log->show();
		}
		else {
          	include_once './classes/tc/stocks.class.php';
    
           	$trans = new stock();
           	$trans->symbol = trim($_REQUEST['symbol']);
           	$trans->name = trim($_REQUEST['name']);
           	$trans->ISIN = trim($_REQUEST['ISIN']);
			if (isset($_REQUEST['skipLookup']) and trim($_REQUEST['skipLookup']) != '')
				$trans->skipLookup = trim($_REQUEST['skipLookup']);
			else
				$trans->skipLookup = 0; // Lookup stock in stock API

           	$trans->insert();
    
            message("success", "Stock added");
                    
            $log = new listStocks();
            $log->show();
		}
    } // addStock
    


    	/**
    	 * removes a stock
    	 */
    	function deleteStock()
    	{
            if ($_SESSION['debug'] == "on"){print "<span class='debug'>deleteStock()</span><br>";}
    
            include_once './classes/tc/stocks.class.php';
    
            $trans = new stock();
            $trans->symbolId = trim($_REQUEST['id']);
    
            $trans->delete();
    	} // deleteAccount
    } // class
?>
