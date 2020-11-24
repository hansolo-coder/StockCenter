<?php
    /**
     * form for adding users
     */
    class formAddUser
    {
    	/**
    	 * holds any errors found in check function
    	 * @var string
    	 */
    	public $errors;
    	
    	
    	
    	function __construct()
    	{
    		$this->errors = "";
    	}
    	
    	
    	/**
    	 * displays the form
    	 */
    	function display()
    	{
            if ($_SESSION['debug'] == "on"){print "<span class='debug'>addUserForm->display()</span><br>";}
    
    		print "<div class='spacer'>";
    		print "</div>";
    		print "<div style='width: 250px; background-color: #E6E6E6; padding: 10px; margin: auto; text-align: center; font-size: 12pt; font-weight: bold;'>";
    		print "Create User";
    		print "</div>";
    		print "<div style='width: 250px; background-color: #E6E6E6; padding: 10px; margin: auto;'>";
    		print "<form action='" . $_SERVER['PHP_SELF'] . "' method='post'>";
    		print "    User ID<br>";
    		print "    <input type='text' name='userId' style='width: 100%;'><br>";
    		print "    Password<br>";
    		print "    <input type='password' name='password' style='width: 100%;'><br>";
    		print "    <input type='hidden' name='action' value='addUser'><br>";
    		print "    <div style='text-align: right;'>";
    		print "        <input type='submit' value='Save'>";
    		print "    </div>";
    		print "</form>";
    		print "</div>";
    	}


		/**
		 * validates form
		 */
		function check()
		{
			# user id
			if(isset($_REQUEST['userId']))
			{
				# check length
				if(strlen(trim($_REQUEST['userId'])) >= 4)
				{
					# check for alpha/numeric characters only
					if(!ctype_alnum(trim($_REQUEST['userId'])))
					{
						# bad characters error
						$this->errors .= "Invalid characters in user ID<br>";
					}
				}
				else 
				{
					# user id too short error
					$this->errors .= "User ID must be at least 4 characters<br>";
				}
			}
			else 
			{
				# missing user id error
				$this->errors .= "No user ID provided<br>";
			}
			
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
    		# check the form
    		$this->check();
    		
    		# if there were no errors...
    		if($this->errors == "")
    		{
    			if ($_SESSION['debug'] == "on"){print "<span class='debug'>addUserForm->process()</span><br>";}
    			
    			# if a data file by the same name doesn't already exist...
   				if (!file_exists("./data/" . strtolower(trim($_REQUEST['userId'])) . ".sqlite"))
   				{
					if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbConnect: addUserForm " . __LINE__ . "</span><br>";}
   					
   					# init the new database
					include_once './classes/db.class.php';
   						 
   					$dbn = new db();
   					$dbn->fileName = strtolower(trim($_REQUEST['userId']));
   					$dbn->password = trim($_REQUEST['password']);
   					$dbn->init();
    			
   					if ($_SESSION['debug'] == "on"){
   						print "<span class='debug'>dbDisconnect: addUserForm " . __LINE__ . "</span><br>";
   					}
    			
   					$dbn = null;
   					$conn = null;
   				}
   				else
   				{
   					# file already exists error
   					message("error", "User name in use, can not create account");
   				}
    		}
    		else 
    		{
    			# display the errors
    			message("error", $this->errors);
    		}
    	}
    }
?>