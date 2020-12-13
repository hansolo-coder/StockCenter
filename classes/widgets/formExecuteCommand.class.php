<?php
    /**
     * form for executing a command to the SQLITE database
     */
    class formExecuteCommand
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
            if ($_SESSION['debug'] == "on"){print "<span class='debug'>executeCommandForm($this->action)</span><br>\n";}
        
    	    # view input form
    	    print "<h3>System Maintenance</h3>\n";
            print "<fieldset>\n";
            print "    <legend>\n";
            print "        Maintain SQLITE database\n";
            print "    </legend>\n";
    	    print "<form id='inpSqlForm' method='post' action='" . htmlentities($_SERVER['PHP_SELF']) . "'>\n";
            print "  <label for='inpSql'>SQL command</label>\n";
            print "  <textarea id='inpSql' name='sqlcommand'  rows='4' cols='50' autofocus maxlength='500' required form='inpSqlForm' placeholder='Input SQL Command to execute'>\n";
            print "PRAGMA integrity_check</textarea>\n";
            print "  <label for='inpKey'>Key</label>\n";
            print "  <input id='inpKey' name='key' type='text' maxlength='30'>\n";
    	    print "  <input type='hidden' name='action' value='" . $this->action . "'>\n";
    	    print "  <input type='submit' value='Execute'>\n";
    	    print "</form>\n";
            print "</fieldset>\n";
        }

    	/**
    	 * process the action
         * TODO Only process if provided key matches key in settings table (which should not be show in settings menu)
    	 */
    	function process()
    	{
            if ($_SESSION['debug'] == "on"){print "<span class='debug'>executeCommandForm($this->action)</span><br>\n";}

            include_once './classes/db.class.php';
    
            if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbConnect: " . __LINE__ . "</span><br>";}
            
            $conn = new db();
            $conn->fileName = $_SESSION['userId'];
            $db=$conn->connect();

            try {
    	      $sql = $_REQUEST['sqlcommand'];
    	      $rs = $db->prepare($sql);
    	      $res = $rs->execute();
              $count = $rs->rowCount();
              message("success", "Command executed:" . $count);
            } catch (PDOException $e) {
              message("error", $e->getMessage());
            }

    	    if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbDisconnect: " . __LINE__ . "</span><br>";}
    		
    	    $row = NULL;
    	    $rs = NULL;
    	    $db = null;
    	    $conn = null;
                    
            include_once './classes/widgets/formExecuteCommand.class.php';
                    
            $log = new formExecuteCommand();
            $log->action = $this->action;
            $log->display();
	}
    }
?>
