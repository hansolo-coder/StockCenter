<?php
    /**
     * form for changing a user's password
     */
    class formChangePassword
    {
    	/**
    	 * holds errors found during check
    	 * @var string
    	 */
    	public $errors;
    	
    	
    	
    	function __construct()
    	{
    		$this->errors = '';
    	}
    	
    	
    	/**
    	 * displays the form
    	 */
    	function display()
    	{
            print "<div class='spacer'></div>";
    		print "<div style='width: 300px; padding: 10px; margin: auto;'>";
    		print "    <form action='" . $_SERVER['PHP_SELF'] . "' method='post'>";
            print "        <table class='data'>";
            print "            <tr>";
            print "                <th class='data' colspan='2'>";
            print "                    Set Password";
            print "                </th>";
            print "            </tr>";
            print "            <tr>";
            print "                <td class='data'>";
            print "    	               New Password";
            print "                </td>";
            print "                <td class='data'>";
    		print "                    <input type='password' name='password' style='width: 100%;'>";
            print "                </td>";
            print "            </tr>";
            print "        </table>";
    		print "    	   <input type='hidden' name='action' value='changePassword'>";
    		print "        <div style='text-align: right;'>";
    		print "            <input type='submit' value='Save'>";
    		print "        </div>";
    		print "    </form>";
    		print "</div>";
    	}
    	
    	
    	/**
    	 * validates the form
    	 */
    	function check()
    	{
    		# password
 			if(isset($_REQUEST['password']))
			{
				# check length
				if(strlen(trim($_REQUEST['password'])) < 7)
				{
					# password too short error
					$this->errors .= "Password must be at least 8 characters<br>";
				}
			}
    	}
    	
    	
    	/**
    	 * processes the form
    	 */
    	function process()
    	{
    		$this->check();
    		
    		# if there were no errors...
    		if($this->errors == '')
    		{
	           	include_once './classes/tc/setting.class.php';
	    
	           	$set = new setting();
	           	$set->settingName = 'password';
	            $set->settingValue = md5(trim($_REQUEST['password']));
	            $set->update();
	    
	            message("success", "Password Changed");
	            homePage();
    		}
    		else 
    		{
    			message("error", $this->errors);
    		}
     	}
    }
?>