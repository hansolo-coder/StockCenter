<?php
    /**
     * manages the stocks
     */
    class listStocks
    {
	public $action;
	public $updateStock;

        /**
         * display the stocks
         */
        function show()
        {
            if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbConnect: listStocks.php " . __LINE__ . "</span><br>";}

            include_once './classes/tc/setting.class.php';
            $sett = new setting();
            $sett->settingName = "yahooFinanceBaseUrl";
            $sett->select();
            $yahooFinanceBaseUrl = $sett->settingValue;

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

            require_once 'contextmenu.php';

            include_once './classes/tc/stockData.class.php';

            $sData = new stockData();

	    if ($this->updateStock) {
		if (!isset($_REQUEST['id'])) {
			$this->updateStock = FALSE;
		}
	    }
	    if ($this->updateStock) {
          	include_once './classes/tc/stocks.class.php';
    
           	$updstock = new stocks();
		$updstock->symbolId = $_REQUEST['id'];
		$updstock->select();
		if ($updstock->inError)
		  $this->updateStock = FALSE;
	    }

            print "<fieldset>\n";
            print "    <legend>\n";
            print "        Stocks\n";
            print "    </legend>\n";
            // TODO flyt style til css
            print "<table class='data manualtable' style='border-collapse: separate; border-spacing: 0;'>\n";
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
            print "    <form action='" . htmlentities($_SERVER['PHP_SELF']) . "' method='post'>\n";
            print "    <tr>\n";
            print "        <input type='hidden' name='action' value='addStock2'>\n";
	    if ($this->updateStock) {
	        print "        <input type='hidden' name='id' value='" . $updstock->symbolId . "'>\n";
	    }
            print "        <td class='data'>\n";
            print "            <input type='text' name='symbol' class='date'";
	    if ($this->updateStock) {
		print " value='" . $updstock->symbol . "'"; // TODO
	    }
	    print ">\n";
            print "        </td>\n";
            print "        <td class='data'>\n";
            print "            <input type='text' name='ISIN' class='medium'";
	    if ($this->updateStock) {
		print " value='" . $updstock->ISIN . "'"; // TODO
	    }
	    print ">\n";
            print "        </td>\n";
            print "        <td class='data'>\n";
            print "            <input type='text' name='name' class='large'";
	    if ($this->updateStock) {
		print " value='" . $updstock->name . "'"; // TODO
	    }
	    print ">\n";
            print "        </td>\n";
            print "        <td class='data'>\n";
            print "            <input type='text' name='skipLookup' class='mini' maxlength='1'";
	    if ($this->updateStock) {
		print " value='" . $updstock->skipLookup . "'"; // TODO
	    }
	    print ">\n";
            print "        </td>\n";
            print "        <td class='data'>\n";
            print "            -\n";
            print "        </td>";
            print "        <td class='data'>\n";
            print "            <input type='submit' value='Save'>\n";
            print "        </td>\n";
            print "    </tr>\n";
            print "    </form>\n";
    
            // set row color based odd/even
            $lineno = 0;
            foreach($rows as $row)
            {
                $lineno = $lineno + 1;
                if ($lineno % 2 == 0)
                    print "    <tr class='data even'>\n";
                else
                    print "    <tr class='data odd'>\n";

                $sData->symbol = $row['symbol'];
                $sData->select();
                $companywebsite = $sData->companywebsite;

                print "        <td class='data task' data-id='" . $row['symbolId'] . "'";
                print            " data-url1='" . urlencode(str_replace('{}', $row['symbol'], $yahooFinanceBaseUrl)) . "'";
                print            " data-url2='" . urlencode($row['URL']) . "'";
		print            " data-url3='" . urlencode($companywebsite) . "'";
                print ">\n";
                print "            <a href='index.php?action=activityLog&symbol=" . $row['symbol'] ."'>" . $row['symbol'] ."</a>\n";
                print "        </td>\n";
                print "        <td class='data'>";
                print                  $row['ISIN'];
                print         "</td>\n";
                print "        <td class='data'>";
                print                  $row['name'];
                print         "</td>\n";
                print "        <td class='data'>";
                print                  (string)$row['SkipLookup'];
                print         "</td>\n";
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
            } elseif (!(isset($_REQUEST['name']) and trim($_REQUEST['name']) != '')) {
                message("error", "Name must be provided");
                $inError = true;
            }
            if(!$inError) {
                include_once './classes/tc/stocks.class.php';
    
                $trans = new stocks();
                $trans->symbol = trim($_REQUEST['symbol']);
                if (isset($_REQUEST['id']) && trim($_REQUEST['id']) != '')
                    $trans->symbolId = $_REQUEST['id'];
                $trans->name = trim($_REQUEST['name']);
                $trans->ISIN = trim($_REQUEST['ISIN']);
                if (isset($_REQUEST['skipLookup']) and trim($_REQUEST['skipLookup']) != '')
                    $trans->skipLookup = trim($_REQUEST['skipLookup']);
                else
                    $trans->skipLookup = 0; // Lookup stock in stock API

                if (isset($_REQUEST['id']) && trim($_REQUEST['id']) != '')
                    $trans->update();
                else
                    $trans->insert();
    
                message("success", "Stock added");
            }
        } // addStock
    


    	/**
    	 * removes a stock
    	 */
    	function deleteStock()
    	{
            if ($_SESSION['debug'] == "on"){print "<span class='debug'>deleteStock()</span><br>";}
    
            include_once './classes/tc/stocks.class.php';
    
            $trans = new stocks();
            $trans->symbolId = trim($_REQUEST['id']);
    
            $trans->delete();
            message("success", "Stock deleted");
    	} // deleteStock
    } // class
?>
