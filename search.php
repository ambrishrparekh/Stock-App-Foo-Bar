<!-- Basic searching implemented. 
    Search result will be displayed in alphabetical order of symbols.
    The keywords will be bolded in the search result
    Showing a blurb when clicked is not yet implemented-->

<!DOCTYPE html>
<html>
<head>
<title>Search Bar</title>
<style>
table {
    font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
    border-collapse: collapse;
    width: 100%;
}

td, th {
    border: 1px solid #ddd;
    padding: 8px;
}

tr:nth-child(even){background-color: #f2f2f2;}

tr:hover {background-color: #ddd;}

th {
    padding-top: 12px;
    padding-bottom: 12px;
    text-align: left;
    background-color: #4CAF50;
    color: white;
}
</style>
<?php
    $host = "localhost";
    $user = "root";
    $pass = "root";
    $db = "StockApp";
    
    $mysqli = new mysqli($host, $user, $pass, $db, 3306);
            
    if ($mysqli->connect_errno) {
        echo "MySQL Connection Error:" . $mysqli->connect_error;
        exit();
    }
    
    if (array_key_exists("searchTerm", $_REQUEST)) {
        $searchTerm = $_REQUEST["searchTerm"];    
        $sql = 'SELECT * FROM Stocks WHERE companyName LIKE "%' . $searchTerm . '%" OR symbol LIKE "%' . $searchTerm . '%";';
    }
    else {
        $sql = "SELECT * FROM Stocks;";
    }
    
    
?>
</head>
<body>
<form name="searchForm" action="search.php">
	<input type="text" name="searchTerm" value="search here">	
	<input type="submit" name="submit" value="Search!">
</form>
<hr>
<?php         
    $results = $mysqli->query($sql);
    if (!$results) {
        echo "SQL ERROR: " . $mysqli->error;
        exit();
    }
    
    // if there is more than one result, display only the results.
    // Else, echo "no results"
    if ($results->num_rows == 0) {
        echo "No Stocks Found";
    }
    else {
        echo '<table><tr><th>Symbol</th><th>Company</th></tr>';
        while ($row = $results->fetch_assoc() ) {
            $boldedSymbol = preg_replace('/' . $searchTerm . '/i', "<strong>$0</strong>", $row["symbol"]);
            $boldedCompany = preg_replace('/' . $searchTerm . '/i', "<strong>$0</strong>", $row["companyName"]);
            // echo symbol
            echo "<tr><td>$boldedSymbol</td><td>$boldedCompany</td></tr>";
        }                
        echo '</table>';
    }

    $mysqli->close();
?>
</body>
</html>
