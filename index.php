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
	    # TODO make dateformat dependent on US/EUR setting
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
        

        # if we have an action to process
        if (isset($_REQUEST['action']) and $_REQUEST['action'] != '')
        {
            if (isset($_REQUEST['action']) and $_REQUEST['action'] == "addStock")
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
                homePage();
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "stocks")
            {
                include_once 'classes/widgets/listStocks.class.php';
                $log = new listStocks();
                $log->show();
            }
			elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "addStock2")
            {
            	include_once 'classes/widgets/listStocks.class.php';

            	$form = new listStocks();
            	$form->process();
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "deleteStock2")
            {
                include_once 'classes/widgets/listStocks.class.php';
                
                $stock = new listStocks();
                $stock->deleteStock();
                homePage();
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "overview")
            {
                include_once './classes/pageHeader.class.php';
                $header = new pageHeader();
                $header->display();
                
                overview();
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "activityLog")
            {
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
                $log->show();
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "addAccount")
            {
                include_once 'classes/widgets/listAccounts.class.php';
                $log = new listAccounts();
                $log->addAccount();
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "dividendReport")
            {
                include_once './classes/pageHeader.class.php';
                $header = new pageHeader();
                $header->display();

                dividendReport();
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "addUserForm")
            {
                include_once './classes/pageHeader.class.php';
                $header = new pageHeader();
                $header->display();

                include_once 'classes/widgets/formAddUser.class.php';
                $user = new formAddUser();
                $user->display();
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "addUser")
            {
                include_once 'classes/widgets/formAddUser.class.php';
                $user = new formAddUser();
                $user->process();
                homePage();
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "screener")
            {
                screener();
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "changePasswordForm")
            {
                include_once './classes/pageHeader.class.php';
                $header = new pageHeader();
                $header->display();

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
                include_once './classes/pageHeader.class.php';
                $header = new pageHeader();
                $header->display();

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
                $header->display();

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
                $header->display();

                include_once 'classes/widgets/formConversionAnalysis.class.php';
                $form = new formConversionAnalysis();
                $form->action = 'conversionStep2';
                $form->display();
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "conversionStep2")
            {
                include_once 'classes/widgets/formConversionAnalysis.class.php';
                $form = new formConversionAnalysis();
                $form->process();
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "charts")
            {
                charts();
            }
            elseif (isset($_REQUEST['action']) and $_REQUEST['action'] == "logout")
            {
                session_destroy();
                
                include_once './classes/login.class.php';
                
                $login = new login();
                $login->displayLogin();
                
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
            homePage();
        }

        $page->end();
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

        $login = new login();
        $login->displayLogin();
    }
?>
