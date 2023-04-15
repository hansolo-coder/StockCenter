<?php
    /**
     * form for updating application settings
     */
    class formSettings
    {
    	/**
    	 * displays the form
    	 */
    	function display()
    	{
            if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbConnect: " . __LINE__ . "</span><br>";}

            include_once './classes/db.class.php';
        	
            # get the settings from the database
            $conn = new db();
            $conn->fileName = $_SESSION['userId'];
            $db=$conn->connect();
            
    	    $sqlSettings = "SELECT * FROM settings";
    	    $rsSettings  = $db->prepare($sqlSettings);
    	    $rsSettings->execute();
    	    $rowSettings = $rsSettings->fetchall();
    
    	    if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbDisconnect: " . __LINE__ . "</span><br>";}
        	
    	    $rsSettings = null;
    	    $db = null;
    	    $conn = null;
    	    
    	    # display the form
    	    print "<div class='spacer'></div>";
    	    print "<div style='width: 85%; color: black; padding: 10px; margin: auto; text-align: center;'>";
    	    print "    <form action='" . $_SERVER['PHP_SELF'] . "' method='post'>";
            print "        <table class='data'>";
            print "            <tr>";
            print "                <th class='data' style='width: 50%'>";
            print "                    Setting Name";
            print "                </th>";
            print "                <th class='data' style='width: 50%'>";
            print "                    Setting Value";
            print "                </th>";
            print "            </tr>";
    
            foreach ($rowSettings as $setting)
            {
                print "            <tr>";
    
                if ($setting['settingName'] != "password" and $setting['settingName'] != "databaseVersion" and $setting['settingName'] != "accessKey")
                {
                    print "                <td class='data'>";
                    print $setting['settingName'] . " (" . $setting['settingDesc'] . ")";
                    print "                </td>";
                    print "                <td class='data'>";
                    print "                    <input type='text' name='" . $setting['settingName'] . "' value='" . $setting['settingValue'] . "' class='widetext'>";
                    print "                </td>";
                }
    
                print "            </tr>";
            }
    
            print "    </table>";
    		print "        <input type='hidden' name='action' value='saveSettings'>";
    		print "        <div style='text-align: right;'>";
    		print "            <input type='submit' value='Update Settings'>";
    		print "        </div>";
    		print "    </form>";
    		print "</div>";
    	}
    	
    	
    	# check the form
    	function check()
    	{
    		
    	}
    	
    	
    	/**
    	 * process the form
    	 */
    	function process()
    	{
            if(isset($_REQUEST['sellTrigger']) and trim($_REQUEST['sellTrigger']) != '' and trim($_REQUEST['sellTrigger']) != '.')
            {
            	include_once './classes/tc/setting.class.php';
            	
            	$set = new setting();
            	$set->settingName = 'sellTrigger';
                $set->settingValue = trim($_REQUEST['sellTrigger']);
                $set->update();
            	$set = new setting();
            	$set->settingName = 'refreshTime';
                $set->settingValue = trim($_REQUEST['refreshTime']);
                $set->update();
            	$set = new setting();
            	$set->settingName = 'stockdataclass';
                $set->settingValue = trim($_REQUEST['stockdataclass']);
                $set->update();
            	$set = new setting();
            	$set->settingName = 'currency';
                $set->settingValue = trim($_REQUEST['currency']);
                $set->update();
            	$set = new setting();
            	$set->settingName = 'showTransactionTax';
                $set->settingValue = trim($_REQUEST['showTransactionTax']);
                $set->update();
            	$set = new setting();
            	$set->settingName = 'region';
                $set->settingValue = trim($_REQUEST['region']);
                $set->update();
            	$set = new setting();
            	$set->settingName = 'chgPctMarkUnchanged';
                $set->settingValue = trim($_REQUEST['chgPctMarkUnchanged']);
                $set->update();
            	$set->settingName = 'enableDeletes';
                $set->settingValue = trim($_REQUEST['enableDeletes']);
                $set->update();
            	$set->settingName = 'yahooFinanceBaseUrl';
                $set->settingValue = trim($_REQUEST['yahooFinanceBaseUrl']);
                $set->update();
    
                message("success", "Settings Saved");
                
                include_once './classes/widgets/formSettings.class.php';
                $this->display();
            }
            else
            {
                message("error", "No value for sellTrigger provided, settings NOT saved");
                settingsForm();
            }
    	}	
    }
