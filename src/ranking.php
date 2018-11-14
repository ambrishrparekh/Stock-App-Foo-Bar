<?php

    session_start();

    include 'databaseConnection.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Rankings</title>
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
	<h2>Rankings</h2>
<?php   
        // get balance of this user
        $sql = "SELECT * FROM Rankings order by r_money desc limit 5;";
        $results = $mysqli->query($sql);
        if (!$results) {
            echo "SQL ERROR: " . $mysqli->error;
            exit();
        }
		//print_r($results);
        if ($results->num_rows >= 1) { 
			//print_r($results);
?>
	
			<table><tr><th>Rank</th><th>Username</th><th>Total Earnings</th></tr>
<?php 
			while($row = $results->fetch_assoc()) {
?>
				<tr><td><?php echo $row["r_ranking"];?></td><td><?php echo $row["r_username"];?></td><td><?php echo $row["r_money"];?></td></tr>
<?php 
				
			}
			?>
			</table>	
				<?php
		}
?>

</body>
</html>
