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
            $sql = "SELECT * FROM accounts ORDER BY accountNumber";
            $rs = $db->prepare($sql);
            $rs->execute();
            $rows = $rs->fetchAll();
    
            if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbDisconnect: listAccounts.php " . __LINE__ . "</span><br>";}
                
            $rs = NULL;
            $db = NULL;
            $conn = NULL;
    
            include_once './classes/pageHeader.class.php';
            $header = new pageHeader();
            $header->display();
                
            print "<div class='spacer'></div>\n";
    
            print "<fieldset>\n";
            print "    <legend>\n";
            print "        Accounts\n";
            print "    </legend>\n";
            print "<table class='data'>\n";
            print "    <tr>\n";
            print "        <th class='data'>\n";
            print "            Number\n";
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
            print "            &nbsp;\n";
            print "        </th>\n";
            print "    </tr>\n";
            print "    <form action='" . htmlentities($_SERVER['PHP_SELF']) . "?action=addAccount' method='post'>\n";
            print "    <tr>\n";
            print "        <td class='data'>\n";
            print "            <input type='text' name='number'>\n";
            print "        </td>\n";
            print "        <td class='data'>\n";
            print "            <input type='text' name='name'>\n";
            print "        </td>\n";
            print "        <td class='data'>\n";
            print "            <input type='text' name='financialinstitution'>\n";
            print "        </td>\n";
            print "        <td class='data'>\n";
            print "            <input type='text' name='created' id='created'>\n";
            print "            <script>\n";
            print "                $(function(){\n";
            print "                    var opts = {\n";
            print "                        dateFormat:'yy-mm-dd'\n";
            print "                    };\n";
            print "                    $( '#created' ).datepicker(opts);\n";
            print "                });\n";
            print "            </script>\n";
            print "        </td>\n";
            print "        <td class='data'>\n";
            print "            <input type='text' name='closed' id='closed'>\n";
            print "            <script>\n";
            print "                $(function(){\n";
            print "                    var opts = {\n";
            print "                        dateFormat:'yy-mm-dd'\n";
            print "                    };\n";
            print "                    $( '#closed' ).datepicker(opts);\n";
            print "                });\n";
            print "            </script>\n";
            print "        </td>\n";
            print "        <td class='data'>\n";
            print "            -\n";
            print "        </td>";
            print "        <td class='data'>\n";
            print "            <input type='submit' value='Save'>\n";
            print "        </td>\n";
            print "    </tr>\n";
            print "    </form>\n";
    
            // set row color based on activity type
            foreach($rows as $row)
            {
                print "    <tr style='background-color: #ABD9FF;'>\n";
                print "        <td class='data'>\n";
                print "            " . $row['accountNumber'];
                print "        </td>\n";
                print "        <td class='data'>\n";
                print "            " . $row['accountName'];
                print "        </td>\n";
                print "        <td class='data'>\n";
                print "            " . $row['financialInstitution'];
                print "        </td>\n";
                print "        <td class='data'>\n";
                print "            " . $row['aCreated'];
                print "        </td>\n";
                print "        <td class='data'>\n";
                print "            " . $row['aClosed'];
                print "        </td>\n";
                print "        <td class='data'>\n";
                print "            <a class='delete' href='index.php?action=deleteAccount&id=" . $row['accountId'] . "'>Delete</a>\n";
                print "        </td>\n";
                print "    </tr>\n";
            } // foreach
    
            print "</table>\n";
            print "</fieldset>\n";
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
