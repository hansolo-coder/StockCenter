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
			if ($_SESSION['debug'] == "on"){print "<span class='debug'>startPage()</span><br>";}
			
			print "<html>\n";
			print "    <head>\n";
			print "        <title>\n";
			print "            Stock Center\n";
			print "        </title>\n";
			print "        <link rel='stylesheet' href='./javascript/jquery-ui/jquery-ui.min.css'>\n";
			print "        <script src='./javascript/jquery-ui/external/jquery/jquery.js'></script>\n";
			print "        <script src='./javascript/jquery-ui/jquery-ui.min.js'></script>\n";
			print "        <script src='./javascript/dataTables/js/jquery.dataTables.min.js'></script>\n";
			print "        <link rel='stylesheet' href='./javascript/dataTables/css/jquery.dataTables.min.css'>\n";
			print "    </head>\n";
			print "    <body>\n";
			print "        <div class='loader'></div>\n";
			print "        <style>\n";
			print "            body{font-family: arial; font-size: 10pt;}\n";
			print "            div.page{width: 1024px; min-height: 600px; border: 0px solid #cfcfcf; margin: auto;}\n";
			print "            div.centered{width: 100%; margin: auto;}\n";
			print "            div.spacer{width: 100%; height: 20px;}\n";
			print "            div.footer{width: 1024px; text-align: center; margin: auto;}\n";
			print "            div.success{padding: 4px; border: 2px solid #19A347; color: #19A347; background-color: #AFFFAB; text-align: center;}\n";
			print "            div.error{padding: 4px; border: 2px solid #FF5036; color: #FF5036; background-color: #FFB6AB; text-align: center;}\n";
			print "            table.data{width: 100%;}\n";
			print "            th.data{background-color: #E6E6E6; padding:3px; font-family: arial; font-size: 10pt; border: 1px solid #cfcfcf;}\n";
			print "            td.data{text-align: center; padding: 3px; font-family: arial; font-size: 10pt; border: 1px solid #cfcfcf;}\n";
			print "            table.menu{width: 100%;}\n";
			print "            td.menu{width: 14.3%; background-color: #E6E6E6; text-align: center; padding: 3px; font-family: arial; font-size: 10pt; border: 1px solid #cfcfcf;}\n";
			print "            a{text-decoration: none; color: #000000;}\n";
			print "            a.delete{color: #ffffff; text-decoration: none; background-color: red; padding-right: 4px; padding-left: 4px;}\n";
			print "            span.heading{font-weight: bold; text-align: center;}\n";
			print "            span.red{color: red;}\n";
			print "            h3{font-size: 10pt; margin: 0px; text-align: center; color: #666666}\n";
			print "            fieldset{border: 1px solid #cfcfcf;}\n";
			print "            legend{font-size: 10pt;}\n";
			print "            span.debug{color: red;}\n";
			print "            .loader {position: fixed;left: 0px;top: 0px;width: 100%;height: 100%;z-index: 9999;background: url('images/page-loader.gif') 50% 50% no-repeat rgb(249,249,249);}\n";
			print "        </style>\n";
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
			print "            &nbsp;\n";
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
			print "            <a href='" . $_SERVER['PHP_SELF'] . "?action=settingsForm'>Settings</a>\n";
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