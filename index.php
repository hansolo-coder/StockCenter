<?php
    session_start();

# error_reporting(E_ALL);
# ini_set("display_errors", 1);

    # -------------------------------------------------------------------------
    # stockcenter: a personal stock portfolio management tool
    # -------------------------------------------------------------------------
    include_once './stockcenter.lib.php';

     # main control block
    if (isset($_SESSION['loggedIn']) and $_SESSION['loggedIn'] == 'y')
    {
        # catch backup and restore actions before we output any data
        if (isset($_REQUEST['action']) and $_REQUEST['action'] == "backup")
        {
            # set the name of the file we want to read
            $dataFile = "./data/" . $_SESSION['userId'] . ".sqlite";

            # read the file & send to the user
            if (isset($_SESSION['region']) and $_SESSION['region'] == 'US')
              header('Content-Disposition: attachment; filename=stock_center_data_' . date('m-d-Y') . ".sqlite");
            else
              header('Content-Disposition: attachment; filename=stock_center_data_' . date('Y-m-d') . ".sqlite");
            header("Content-type: application/x-sqlite3");
            readfile($dataFile);

            # display the home page
            include_once './classes/page.class.php';
            $page = new page();
            $page->start();
            
            homePage();
            $page->end();
            exit();
        }
        elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "restoreStep2")
        {
        	include_once './classes/restoreData.class.php';

        	$r = new restoreData();
        	$r->step2();
        }
        # end backup and restore block

        
        
        # begin other actions block
        include_once './classes/page.class.php';
        $page = new page();
        $page->start();

	$showDeleteOption = getSettingValueBool("enableDeletes");  

        # if we have an action to process
        if (isset($_REQUEST['action']) and $_REQUEST['action'] != '')
        {
            // $_REQUEST['action'] IS set and non-blank so no need to check more
            if ($_REQUEST['action'] == "showExecuteCommand")
            {
            	include_once 'classes/widgets/formExecuteCommand.class.php';

            	$form = new formExecuteCommand();
                $form->action = "executeCommand";
            	$form->display();
            }
            elseif ($_REQUEST['action'] == "executeCommand")
            {
            	include_once 'classes/widgets/formExecuteCommand.class.php';

            	$form = new formExecuteCommand();
                $form->action = "showExecuteCommand";
            	$form->process();
            }
            elseif ($_REQUEST['action'] == "addStock")
            {
            	include_once 'classes/widgets/formAddStock.class.php';

            	$form = new formAddStock();
            	$form->process();
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "deleteStock")
            {
                include_once 'classes/widgets/formDeleteStock.class.php';
                
                $stock = new formDeleteStock();
                $stock->deleteStock();
                homePage($showDeleteOption);
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "stocks")
            {
                include_once './classes/pageHeader.class.php';
                $header = new pageHeader();
                $header->display($showDeleteOption);

                include_once 'classes/widgets/listStocks.class.php';
                $log = new listStocks();
                $log->show();
            }
            elseif (isset($_REQUEST['action']) and ($_REQUEST['action'] == "addStock2" or $_REQUEST['action'] == "updateStock2"))
            {
            	include_once 'classes/widgets/listStocks.class.php';

            	$form = new listStocks();
                $form->action = $_REQUEST['action'];
		$form->updateStock = FALSE;
		if ($_REQUEST['action'] == "updateStock2") {
		  $form->updateStock = TRUE;
		} else {
            	  $form->addStock();
		}

                include_once './classes/pageHeader.class.php';
                $header = new pageHeader();
                $header->display($showDeleteOption);

		$form->show();
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "deleteStock2")
            {
                include_once 'classes/widgets/listStocks.class.php';
                
                $stock = new listStocks();
                $stock->action = $_REQUEST['action'];
                $stock->deleteStock($showDeleteOption);

                include_once './classes/pageHeader.class.php';
                $header = new pageHeader();
                $header->display($showDeleteOption);

                $stock->show();
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "overview")
            {
                include_once './classes/pageHeader.class.php';
                $header = new pageHeader();
                $header->display($showDeleteOption);

                $showForAccount = NULL;
                if (isset($_REQUEST['account']))
                    $showForAccount = $_REQUEST['account'];
                overview($showForAccount);
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "activityLog")
            {
                include_once './classes/pageHeader.class.php';
                $header = new pageHeader();
                $header->display($showDeleteOption);
                
                include_once 'classes/widgets/listTransactionLog.class.php';
                $log = new listTransactionLog();
                $log->symbol = $_REQUEST['symbol'];
                $log->showLog();
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "addTransaction")
            {
                include_once 'classes/widgets/listTransactionLog.class.php';
                $log = new listTransactionLog();
                $log->addTransaction();
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "setStockPrice")
            {
                include_once 'classes/widgets/listTransactionLog.class.php';
                $log = new listTransactionLog();
                $log->setStockPrice();

                message("success", "Fixed stock data updated");
                
                $log->symbol = $_REQUEST['symbol'];
                $log->showLog();
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "deleteTransaction")
            {
                include_once 'classes/widgets/listTransactionLog.class.php';
                $log = new listTransactionLog();
                $log->deleteTransaction();

                message("success", "Transaction Deleted");
                
                $log->symbol = $_REQUEST['symbol'];
                $log->showLog();
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "accounts")
            {
                include_once 'classes/widgets/listAccounts.class.php';
                $log = new listAccounts();
                $log->show($showDeleteOption);
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "addAccount")
            {
                include_once 'classes/widgets/listAccounts.class.php';
                $log = new listAccounts();
                $log->addAccount($showDeleteOption);
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "deleteAccount")
            {
                include_once 'classes/widgets/listAccounts.class.php';
                $log = new listAccounts();
                $log->deleteAccount();
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "dividendReport")
            {
                include_once './classes/pageHeader.class.php';
                $header = new pageHeader();
                $header->display($showDeleteOption);

                $forYear = date('Y');
                if (isset($_REQUEST['year']) and is_numeric($_REQUEST['year']))
                {
                  $forYear = substr($_REQUEST['year'],0,4) + 0;
                }
                dividendReport($forYear);
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "addUserForm")
            {
                include_once 'classes/widgets/formAddUser.class.php';
                $user = new formAddUser();
                $user->display();
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "addUser")
            {
                include_once 'classes/widgets/formAddUser.class.php';
                $user = new formAddUser();
                $user->process();
                homePage($showDeleteOption);
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "screener")
            {
                screener();
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "changePasswordForm")
            {
                include_once 'classes/widgets/formChangePassword.class.php';
                $form = new formChangePassword();
                $form->display();
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "changePassword")
            {
                include_once 'classes/widgets/formChangePassword.class.php';
                $form = new formChangePassword();
                $form->process();
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "settingsForm")
            {
                include_once 'classes/widgets/formSettings.class.php';
                $form = new formSettings();
                $form->display();
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "saveSettings")
            {
                include_once 'classes/widgets/formSettings.class.php';
                $form = new formSettings();
                $form->process();
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "signals")
            {
                include_once './classes/pageHeader.class.php';
                $header = new pageHeader();
                $header->display($showDeleteOption);

                signals();
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "restoreStep1")
            {
                include_once './classes/restoreData.class.php';

                $r = new restoreData();
                $r->step1();
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "conversionStep1")
            {
                include_once './classes/pageHeader.class.php';
                $header = new pageHeader();
                $header->display($showDeleteOption);

                include_once 'classes/widgets/formConversionAnalysis.class.php';
                $form = new formConversionAnalysis();
                $form->action = 'conversionStep2';
                $form->display();
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "conversionStep2")
            {
                include_once './classes/pageHeader.class.php';
                $header = new pageHeader();
                $header->display($showDeleteOption);
            
                include_once 'classes/widgets/formConversionAnalysis.class.php';
                $form = new formConversionAnalysis();
                $form->process();
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "charts")
            {
		include_once './classes/pageHeader.class.php';
		$header = new pageHeader();
		$header->display($showDeleteOption);

		include_once './classes/widgets/formCharts.class.php';
		$charts = new formCharts();

		$charts->action = "showAll";
		$charts->displayAll();

		$charts->printExecuteScripts();
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "showTax")
            {
                $forYear = date('Y') - 1;
                if (isset($_REQUEST['year']) and is_numeric($_REQUEST['year']))
                {
                  $forYear = substr($_REQUEST['year'],0,4) + 0;
                }

                include_once 'classes/widgets/listTax.class.php';
                $form = new listTax();
                $form->action = 'showTax';
                $form->display($forYear);
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "logout")
            {
                session_destroy();
                
                include_once './classes/login.class.php';
                
                $login = new login();
                $login->displayLogin(NULL);
                
                exit();
            }
            else
            {
                # unknown action passed, display error
                print "Invalid action passed";
            }
        }
        else
        {
            # no action passed, display the landing page
            homePage($showDeleteOption);
        }

        $page->end();
    } // NOT logged in
    elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == 'dailyStatus' and isset($_REQUEST['xuserz']) and isset($_REQUEST['access']))
    {
        if (logDailyStatus($_REQUEST['action'], $_REQUEST['xuserz'], $_REQUEST['access']))
          http_response_code(200);
        else
          http_response_code(403);
    }
    elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "login")
    {
        # login form has been submitted, validate the user
        include_once './classes/login.class.php';

        $login = new login();
        $login->processLogin();
    }
    else
    {
        # user is not logged in, display the login form
        include_once './classes/login.class.php';

	$username = NULL;
	if (isset($_REQUEST['xuserz']))
	  $username = $_REQUEST['xuserz'];

        $login = new login();
        $login->displayLogin($username);
    }
?>
