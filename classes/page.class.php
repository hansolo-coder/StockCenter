<?php
	/**
	 * handles boilerplate page code
	 */
	class page
	{
		/**
		 * generates the beginning code for a page
		 */
		function start()
		{
			if ($_SESSION['debug'] == "on"){ print "<span class='debug'>startPage()</span><br>"; }
			print "<!DOCTYPE html>\n";
			print "<html>\n";
			print "    <head>\n";
			print "        <title>Stock Center</title>\n";
			print "        <meta charset='UTF-8'>\n";
			print "        <meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>\n";
			print "        <meta http-equiv='Cache-Control' content='no-cache, must-revalidate'>\n";
			print "        <meta http-equiv='Pragma' content='no-cache'>\n";
			print "        <meta http-equiv='Expires' content='0'>\n";
			print "        <link rel='stylesheet' media='screen and (min-device-width: 1001px)' type='text/css' href='assets/style.css'>\n";
			print "        <link rel='stylesheet' media='screen and (max-device-width: 1000px)' type='text/css' href='assets/style800.css'>\n";
			print "\n";
			print "        <link rel='stylesheet' href='./javascript/jquery-ui/jquery-ui.min.css'>\n";
			print "        <script src='./javascript/jquery-ui/external/jquery/jquery.js'></script>\n";
			print "        <script src='./javascript/jquery-ui/jquery-ui.min.js'></script>\n";
			print "        <script src='./javascript/dataTables/js/jquery.dataTables.min.js'></script>\n";
			print "        <link rel='stylesheet' href='./javascript/dataTables/css/jquery.dataTables.min.css'>\n";
			print "    </head>\n";
			print "    <body>\n";
			print "        <div class='loader'></div>\n";
			print "        <script>\n";
			print "            $(window).load(function() {\n";
			print "                $('.loader').fadeOut('slow');\n";
			print "            })\n";
			print "        </script>\n";
			print "        <div class='spacer'></div>\n";
			print "        <div class='page'>\n";
			# menu
			print "<table class='data'>\n";
			print "    <tr>\n";
			print "        <td colspan='3' class='data'>\n";
			print "<table class='menu'>\n";
			print "    <tr>\n";
			print "        <td class='menu'>\n";
			print "            <a href='" . $_SERVER['PHP_SELF'] . "?action=overview'>Overview</a>\n";
			print "        </td>\n";
			print "        <td class='menu'>\n";
			print "            <a href='" . $_SERVER['PHP_SELF'] . "?action=signals'>Signals</a>\n";
			print "        </td>\n";
			print "        <td class='menu'>\n";
			print "            <a href='" . $_SERVER['PHP_SELF'] . "?action=dividendReport'>Dividend Report</a>\n";
			print "        </td>\n";
			print "        <td class='menu'>\n";
			print "            <a href='" . $_SERVER['PHP_SELF'] . "?action=conversionStep1'>Conversion Analysis</a>\n";
			print "        </td>\n";
			print "        <td class='menu'>\n";
			print "            <a href='" . $_SERVER['PHP_SELF'] . "?action=settingsForm'>Settings</a>\n";
			print "        </td>\n";
			print "        <td class='menu'>\n";
			print "            &nbsp;\n";
			print "        </td>\n";
			print "        <td class='menu'>\n";
			print "            <a href='" . $_SERVER['PHP_SELF'] . "?action=logout'>Logout</a>\n";
			print "        </td>\n";
			print "    </tr>\n";
			print "    <tr>\n";
			print "        <td class='menu'>\n";
			print "            <a href='" . $_SERVER['PHP_SELF'] . "?action=accounts'>Accounts</a>\n";
			print "        </td>\n";
			print "        <td class='menu'>\n";
			print "            <a href='" . $_SERVER['PHP_SELF'] . "?action=stocks'>Stocks</a>\n";
			print "        </td>\n";
			print "        <td class='menu'>\n";
			print "            <a href='" . $_SERVER['PHP_SELF'] . "?action=changePasswordForm'>Change Password</a>\n";
			print "        </td>\n";
			print "        <td class='menu'>\n";
			print "            <a href='" . $_SERVER['PHP_SELF'] . "?action=backup'>Backup Datafile</a>\n";
			print "        </td>\n";
			print "        <td class='menu'>\n";
			print "            <a href='" . $_SERVER['PHP_SELF'] . "?action=restoreStep1'>Restore Datafile</a>\n";
			print "        </td>\n";
			print "        <td class='menu'>\n";
			print "            &nbsp;\n";
			print "        </td>\n";
	
			if ($_SESSION['userId'] == "admin")
			{
	
				print "        <td class='menu'>\n";
				print "            <a href='" . $_SERVER['PHP_SELF'] . "?action=addUserForm'>Add User</a>\n";
				print "        </td>\n";
	
			}
	
			print "    </tr>\n";
			print "</table>\n";
			print "        </td>\n";
			print "    </tr>\n";
			print "</table>\n";
		}
		
		
		/**
		 * generates the ending code for a page
		 */
		function end()
		{
			if ($_SESSION['debug'] == "on"){print "<span class='debug'>endPage()</span><br>\n";}
			
			print "        </div>\n";
			
			# footer
			print "        <div class='spacer'></div>\n";
			print "        <div class='footer'>\n";
			print "            <table class='data'>\n";
			print "                <tr>\n";
			print "                    <td class='data' style='background-color: #E6E6E6;'>\n";
			
			global $version;
			print "<a href='http://sourceforge.net/projects/stockcenter/'>Stock Center v" . $version . "</a>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;\n";
			print "Copyright &copy; 2015  David Hieber&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;\n";
			
			print "                        License: <a href='http://opensource.org/licenses/MIT'>MIT License</a>\n";
			print "                    </td>\n";
			print "                </tr>\n";
			print "            </table>\n";
			print "        </div>\n";
			
			# debug block
			if ($_SESSION['debug'] == "on")
			{
				print "        <div>\n";
				print "            UserId = " . $_SESSION['userId'] . "\n";
				print "            <br>\n";
				print "            Debug = " . $_SESSION['debug'] . "\n";
				print "            <br>\n";
			
				if (isset($_SESSION['loggedIn']))
				{
					print "Logged In = " . $_SESSION['loggedIn'] . "\n";
				}
			
				print "            <br>\n";
			
				if (isset($_SESSION['refreshTime']))
				{
					print "Refresh Time = " . $_SESSION['refreshTime'] . "\n";
				}
			
				print "        </div>\n";
			}
			
			print "    </body>\n";
			print "</html>\n";
		}
	}
?>
