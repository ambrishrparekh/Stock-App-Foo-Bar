<?php
    session_start();
       
    if (array_key_exists("signedIn", $_SESSION) && $_SESSION["signedIn"] === TRUE) {
        $username = $_SESSION["username"];
        
        include 'databaseConnection.php';
        
        // Check if the username already exists
        $sql = "SELECT * FROM Investments WHERE i_username=\"$username\";";
        $results = $mysqli->query($sql);
        if (!$results) {
            echo "SQL ERROR: " . $mysqli->error;
            exit();
        }
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
            width: 100%;
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
        if ($results->num_rows >= 1) {
?>
	       		<table><tr><th>Symbol</th><th>Amount</th></tr>
<?php 
    	    while($row = $results->fetch_assoc()) {
?>
    	        <tr><td><?php echo $row["i_symbol"];?></td><td><?php echo $row["i_amount"];?></td>
    	        <td><form action="invest.php?symbol=<?php echo $row["i_symbol"];?>" method="post">Buy <input type="number" name="buyAmount" min="0">
    	        <input type="submit" name="submit" value="Buy"></form><br />
    	        <form action="invest.php?symbol=<?php echo $row["i_symbol"];?>" method="post">Sell <input type="number" name="buyAmount" min="0" max="<?php echo $row["i_amount"];?>">
    	        <input type="submit" name="submit" value="Sell"></form>       
<?php 
    	    }
        }
        else {
            echo "<strong>Follow a stock to start investing!<strong>";
        }
        $mysqli->close();
    }
    else {
        header("Location: signin.php");
        exit();
    }   
?>

</body>
</html>
