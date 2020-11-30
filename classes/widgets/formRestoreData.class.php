<?php
	/**
	 * form for restoring data from backup
	 */
	class formRestoreData
	{
		/**
		 * displays the restore data form
		 */
		function display()
		{
			include_once './classes/pageHeader.class.php';
			$header = new pageHeader();
			$header->display();
		    
			print "<div class='spacer'></div>\n";
			print "<div style='width: 400px; padding: 10px; margin: auto;'>\n";
			print "<form action='" . htmlentities($_SERVER['PHP_SELF']) . "' method='post' enctype='multipart/form-data'>\n";
			print "        <table class='data'>\n";
			print "            <tr>\n";
			print "                <th class='data' colspan='2'>\n";
			print "                    Restore Data\n";
			print "                </th>\n";
			print "            </tr>\n";
			print "            <tr>\n";
			print "                <td class='data'>\n";
			print "    	               Select File\n";
			print "                </td>\n";
			print "                <td class='data'>\n";
			print "                    <input type='file' name='upload' style='width: 100%;'>\n";
			print "                </td>\n";
			print "            </tr>\n";
			print "        </table>\n";
			print "        <input type='hidden' name='action' value='restoreStep2'>\n";
			print "        <div style='text-align: right;'>\n";
			print "            <input type='submit' value='Restore'>\n";
			print "        </div>\n";
			print "    </form>\n";
			print "</div>\n";
		}

		/**
		 * validates the form
		 */
		function check()
		{
			
		}

		/**
		 * processes the form
		 */
		function process()
		{
			# restore the file
			if (isset($_FILES["upload"]["tmp_name"]) and $_FILES["upload"]["tmp_name"] != "")
			{
				# If we have an upload, remove the existing data file
				unlink("./data/" . $_SESSION['userId'] . ".sqlite");

				include_once './classes/page.class.php';
				$page= new page();
				$page->start();

				# copy the uploaded file to the user's data file
				if (move_uploaded_file($_FILES["upload"]["tmp_name"], "./data/" . $_SESSION['userId'] . ".sqlite"))
				{
					message("success", "Data Successfully Restored");
				}
				else
				{
					message("error", "Restore Unsuccessful");
				}

				# display the home page
				homePage();
				$page->end();
				exit();
			}
			else
			{
				# display the home page
				include_once './classes/page.class.php';
				$page = new page();
				$page->start();
				
				message("error", "No File Provided");
				homePage();
				$page->end();
				exit();
			}
		}
	}
?>