<?php
    session_start();
    //ContextOptions for API connection to get current price
    $arrContextOptions=array(
        "ssl"=>array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
        ),
    );
    if (array_key_exists("signedIn", $_SESSION) && $_SESSION["signedIn"] === TRUE) {
        $username = $_SESSION["username"];
        
        include 'databaseConnection.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Money</title>
    <style>
        input {
            width:50px;
        }
        table {
            font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
            border-collapse: collapse;
            width: 800px;
        }

        td, th {
            border: 1px solid #ddd;
            padding: 8px;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #ddd;
        }

        th {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: left;
            background-color: #4CAF50;
            color: white;
        }
    </style>
</head>
<body>
<?php   
        // get balance of this user
        $sql = "SELECT * FROM Users WHERE u_username=\"$username\";";
        $results = $mysqli->query($sql);
        if (!$results) {
            echo "SQL ERROR: " . $mysqli->error;
            exit();
        }
        $balance = (float) $results->fetch_assoc()["u_balance"];
        $balance = $balance / 100.0;
?>
<h3>My balance: $<?php echo number_format($balance, 2);?></h3>
<?php 
        
        // get all investment data of this user
        $sql = "SELECT * FROM Investments WHERE i_username=\"$username\" ORDER BY i_symbol;";
        $results = $mysqli->query($sql);
        if (!$results) {
            echo "SQL ERROR: " . $mysqli->error;
            exit();
        }
        if ($results->num_rows >= 1) {            
?>
	       		<table><tr><th>Symbol</th><th>Amount</th><th>Current Price</th></tr>
<?php 
    	    while($row = $results->fetch_assoc()) {
?>
    	        <tr><td><?php echo $row["i_symbol"];?></td><td><?php echo $row["i_amount"];?></td>
    	        <td align="right">
    	        <?php 
        	        $price = file_get_contents("https://api.iextrading.com/1.0/stock/".urlencode($row["i_symbol"])."/price", false, stream_context_create($arrContextOptions));
        	        if ((float)$price < 0) {
        	            $price = "Price not available";
        	        }
        	        else {
        	        $price = number_format((float)$price, 2);
        	        }
        	        echo "$$price";
    	        ?>
    	        </td>
    	        <td><form action="invest.php?symbol=<?php echo urlencode($row["i_symbol"]);?>" method="post">
    	        Buy <input type="number" name="buyAmount" min="1" value="1">
    	        <input type="submit" name="submit" value="Buy"></form>
	    	<?php if ($row["i_amount"] != 0) {?>
    	        <br />
    	        <form action="invest.php?symbol=<?php echo urlencode($row["i_symbol"]);?>" method="post">
    	        Sell <input type="number" name="sellAmount" min="1" max="<?php echo $row["i_amount"];?>" value="1">
    	        <input type="submit" name="submit" value="Sell"></form></td></tr>
	        <?php } ?>
<?php 
    	    }
    	    echo "</table>";
            $mysqli->close();
            if (array_key_exists("status", $_REQUEST)) {
                $status = $_REQUEST["status"];
                if($status == "success") {
                    echo "<strong>Transaction Successful</strong>";
                }
                else if ($status == "lowBalance") {
                    echo "<strong>Not enough money to confirm transaction</strong>";            
                }
            }
        }
        else {
            echo "<strong>Follow a stock to start investing!<strong>";
        }
    }
    else {
        header("Location: signin.php");
        exit();
    }
?>
<br />
<a href="search.php">Search Stocks</a>
</body>
</html>
