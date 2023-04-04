<?php
    session_start();

    if (isset($_SESSION['loggedIn']) and $_SESSION['loggedIn'] == 'y')
    {
    	  if ($_SESSION['debug'] == "on"){
    		print "<span class='debug'>getStockList</span><br>";
    	  }

          include_once './../classes/db.class.php';

          if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbConnect: " . __LINE__ . "</span><br>";}
    	
          $conn = NULL;
          $db = NULL;
          $rs = NULL;
          $row = NULL;
          try {
            $conn = new db();
            $conn->fileName = trim($_SESSION['userId']);
            $db=$conn->connect();

 	    $sqlStockList = "SELECT symbol FROM stocks WHERE SkipLookup = 0 ORDER BY symbol";
	    $rsStockList = $db->prepare($sqlStockList);
	    $rsStockList->execute();
            $rows = $rsStockList->fetchall();
            $symbols = array();

            foreach($rows as $row) {
                array_push($symbols,$row['symbol']);
            }

            if ($_SESSION['debug'] == "on"){print "<span class='debug'>dbDisconnect: " . __LINE__ . "</span><br>";}
            $row = null;
            $rs = null;
            $db = null;
            $conn = null;

            header("Content-type: application/json; charset=utf-8");
            $json = json_encode($symbols);
            if ($json === false) {
                // Avoid echo of empty string (which is invalid JSON), and JSONify the error message instead:
                $json = json_encode(["jsonError" => json_last_error_msg()]);
                if ($json === false) {
                    // This should not happen, but we go all the way now:
                    $json = '{"jsonError":"unknown"}';
                }
                // Set HTTP response status code to: 500 - Internal Server Error
                http_response_code(500);
             }
             echo $json;
	  } catch (Exception $e) {
            if ($_SESSION['debug'] == "on"){print "<span class='debug'>getStockList: " . __LINE__ . ":" . $e->getMessage() . "</span><br>";}
            $row = null;
            $rs = null;
            $db = null;
            $conn = null;
echo "Err:" . $e->getMessage();
            http_response_code(403);
	  }


    } else {
        http_response_code(401);
    }
    exit();
?>
