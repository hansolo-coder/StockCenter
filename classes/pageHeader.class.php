<?php
    /**
     * manages the menu and action blocks
     */
    class pageHeader
    {
        /**
         * displays the menu and action blocks
         */
        function display()
        {
            if ($_SESSION['debug'] == "on"){print "<span class='debug'>pageHeader()</span><br>\n";}
    
		print "<table class='data'>\n";
		print "    <tr>\n";
    		print "        <td class='data' width='33%'>\n";
    
    		include_once './classes/widgets/formAddStock.class.php';
    		
    		$form = new formAddStock();
    		$form->action = 'addStock';
    		$form->display();
    
    		print "        </td>\n";
    		print "        <td width='33%' class='data'>\n";
    		
    		include_once './classes/widgets/formViewTransactions.class.php';
    		
    		$activityForm = new formViewTransactions();
    		$activityForm->action = 'activityLog';
    		$activityForm->display();
    		
    		print "        </td>\n";
    		print "        <td width='33%' class='data'>\n";
    		
    		include_once './classes/widgets/formDeleteStock.class.php';
    		
    		$dform = new formDeleteStock();
    		$dform->action = 'deleteStock';
    		$dform->display();
    
    		print "        </td>\n";
    		print "    </tr>\n";
    		print "</table>\n";
        }
    }
?>
