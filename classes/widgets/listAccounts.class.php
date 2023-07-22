<?php
    /**
     * manages the accounts
     */
    class listAccounts
    {
        /**
         * display the accounts
         */
        function show()
        {
            if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbConnect: listAccounts.php " . __LINE__ . "</span><br>";}
    
            include_once './classes/db.class.php';
    
            $conn = new db();
            $conn->fileName = $_SESSION['userId'];
            $db=$conn->connect();
    
            // get accounts
            $sql = "SELECT * FROM accounts ORDER BY aCreated DESC";
            $rs = $db->prepare($sql);
            $rs->execute();
            $rows = $rs->fetchAll();
    
            if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbDisconnect: listAccounts.php " . __LINE__ . "</span><br>";}
                
            $rs = NULL;
            $db = NULL;
            $conn = NULL;
    
            print "<div class='spacer'></div>\n";
    
            print "<fieldset>\n";
            print "    <legend>\n";
            print "        Accounts\n";
            print "    </legend>\n";
            // TODO flyt style til css
            print "<table class='display manualtable' id='aoverview' style='width: 100%; xtable-layout:fixed; border-collapse: separate;
border-spacing: 0;'>\n";
            print "   <thead>\n";
            print "    <tr>\n";
            print "        <th class='data'>\n";
            print "            Account\n";
            print "        </th>\n";
            print "        <th class='data'>\n";
            print "            Name\n";
            print "        </th>\n";
            print "        <th class='data'>\n";
            print "            Financial Institution\n";
            print "        </th>\n";
            print "        <th class='data'>\n";
            print "            Created\n";
            print "        </th>\n";
            print "        <th class='data'>\n";
            print "            Closed\n";
            print "        </th>\n";
            print "        <th class='data'>\n";
            print "            UseId\n";
            print "        </th>\n";
            print "        <th class='data'>\n";
            print "            &nbsp;\n";
            print "        </th>\n";
            print "    </tr>\n";
			// https://datatables.net/forums/discussion/41314/how-to-fix-a-row-with-sorting-enabled
            print "    <tr>\n";
            print "    <form action='" . htmlentities($_SERVER['PHP_SELF']) . "' method='post'>\n";
            print "        <td class='data'>\n";
            print "            <input type='hidden' name='accountId' value=''>\n";
            print "            <input type='hidden' name='action' value='addAccount'>\n";
            print "            <input type='text' name='number' class='medium' required>\n";
            print "        </td>\n";
            print "        <td class='data'>\n";
            print "            <input type='text' name='name' class='large' required>\n";
            print "        </td>\n";
            print "        <td class='data'>\n";
            print "            <input type='text' name='financialinstitution' class='large' required>\n";
            print "        </td>\n";
            print "        <td class='data'>\n";
            print "            <input type='text' name='created' id='created' class='date' required>\n";
            print "            <script>\n";
            print "                $(function(){\n";
            print "                    var opts = {\n";
            if (isset($_SESSION['region']) and $_SESSION['region'] == 'US')
              print "                        dateFormat:'mm-dd-yy'\n";
            else
              print "                        dateFormat:'yy-mm-dd'\n";
            print "                    };\n";
            print "                    $( '#created' ).datepicker(opts);\n";
            print "                });\n";
            print "            </script>\n";
            print "        </td>\n";
            print "        <td class='data'>\n";
            print "            <input type='text' name='closed' id='closed' class='date'>\n";
            print "            <script>\n";
            print "                $(function(){\n";
            print "                    var opts = {\n";
            if (isset($_SESSION['region']) and $_SESSION['region'] == 'US')
              print "                        dateFormat:'mm-dd-yy'\n";
            else
              print "                        dateFormat:'yy-mm-dd'\n";
            print "                    };\n";
            print "                    $( '#closed' ).datepicker(opts);\n";
            print "                });\n";
            print "            </script>\n";
            print "        </td>\n";
            print "        <td class='data'>\n";
            print "            <input type='text' name='redirectAccountId' class='mini' required>\n";
            print "        </td>\n";
            print "        <td class='data'>\n";
            print "            <input type='submit' value='Save'>\n";
            print "        </td>\n";
            print "    </form>\n";
            print "    </tr>\n";
            print "   </thead>\n";
            print "   <tbody>\n";
    
            // set row color based odd/even
            $lineno = 0;
            foreach($rows as $row)
            {
                $lineno = $lineno + 1;
                if ($lineno % 2 == 0)
                  print "    <tr class='even'>\n";
                else
                  print "    <tr class='odd'>\n";
                print "        <td class='data'>\n";
                print "            <a href='" . htmlentities($_SERVER['PHP_SELF']) . "?action=overview&account=" . $row['accountId'] . "'>" . $row['accountNumber'] . "</a>\n";
                print "        </td>\n";
                print "        <td class='data'>";
                print              $row['accountName'];
                print         "</td>\n";
                print "        <td class='data'>";
                print              $row['financialInstitution'];
                print         "</td>\n";
                print "        <td class='data'>";
                print              $row['aCreated'];
                print         "</td>\n";
                print "        <td class='data'>";
                print              $row['aClosed'];
                print         "</td>\n";
                print "        <td class='data'>";
                print              $row['redirectAccountId'];
                print         "</td>\n";
                print "        <td class='data'>\n";
                print "            <a class='delete' href='index.php?action=deleteAccount&id=" . $row['accountId'] . "'>Delete</a>\n";
                print "        </td>\n";
                print "    </tr>\n";
            } // foreach
    
            print "   </tbody>\n";
            print "</table>\n";
            print "</fieldset>\n";
            //print "<script>\n";
            //print "    $(document).ready(function(){\n";
            //print "        $('#aoverview').DataTable({'pageLength':15, 'lengthMenu':[5, 15, 30, 50], 'orderCellsTop': true, 'aaSorting': [[ 3, 'desc' ]]});\n";
            //print "    });\n";
            //print "</script>\n";
        } // show
        
        
        
    /**
     * adds a new transaction to the log
     */
    function addAccount()
    {
        if ($_SESSION['debug'] == "on"){print "<span class='debug'>listAccounts-> addAccount()</span><br>";}
    
        if(isset($_REQUEST['number']) and trim($_REQUEST['number']) != '')
        { // number exists
            if(isset($_REQUEST['financialinstitution']) and trim($_REQUEST['financialinstitution']) != '')
            { // financialinstitution exists
             	include_once './classes/tc/account.class.php';
    
              	$trans = new account();
               	$trans->accountNumber = trim($_REQUEST['number']);
               	$trans->accountName = trim($_REQUEST['name']);
               	$trans->financialInstitution = trim($_REQUEST['financialinstitution']);
               	$trans->created = trim($_REQUEST['created']);
               	$trans->closed = trim($_REQUEST['closed']);
		if (isset($_REQUEST['isPension']))
               	  $trans->isPension = trim($_REQUEST['isPension']);
		else
               	  $trans->isPension = 'N';
		if (isset($_REQUEST['accountType']))
               	  $trans->accountType = trim($_REQUEST['accountType']);
		else
               	  $trans->accountType = "Ordinary";
		if (isset($_REQUEST['accountCurrency']))
               	  $trans->accountCurrency = trim($_REQUEST['accountCurrency']);
		else
               	  $trans->accountCurrency = trim($_SESSION['DefaultCurrency']);
		$trans->redirectAccountId = trim($_SESSION['redirectAccountId']);
   
               	$trans->insert();
    
                message("success", "Account added");
                    
                $log = new listAccounts();
                $log->show();
            } // financialinstitution exists
            else
            { // financialinstitution exists
                # no financialinstitution provided
                message("error", "No Financial Institution name provided");
                    
                $log = new listAccounts();
                $log->show();
            } // financialinstitution exists
        } // number exists
        else
        { // number exists
            # no date provided
            message("error", "No AccountNumber Provided");
                
            $log = new listAccounts();
            $log->show();
        } // number exists
    } // addAccount
    


    	/**
    	 * removes a transaction from the log
    	 */
    	function deleteAccount()
    	{
            if ($_SESSION['debug'] == "on"){print "<span class='debug'>deleteAccount()</span><br>";}
    
            include_once './classes/tc/account.class.php';
    
            $trans = new account();
            $trans->accountId = trim($_REQUEST['id']);
    
            $trans->delete();
    	} // deleteAccount
    } // class
?>
