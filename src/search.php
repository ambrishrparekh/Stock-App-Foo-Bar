<!-- Basic searching implemented.
Search result will be displayed in alphabetical order of symbols.
The keywords will be bolded in the search result
Showing a blurb when clicked is not yet implemented-->
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
        $sql = "SELECT * FROM Stocks WHERE companyName LIKE '%" . $searchTerm . "%' OR symbol LIKE '%" . $searchTerm . "%';";
    }
    else
    {
        $searchTerm = "";
        $sql = "SELECT * FROM Stocks;";
    }

    $results = $mysqli->query($sql);
    if (!$results) {
        echo "SQL ERROR: " . $mysqli->error;
        exit();
    }

    $mysqli->close();
?>
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
    <form name="searchForm" action="search.php">
        <input type="text" name="searchTerm"
            value="<?php if ($searchTerm) {
                echo $searchTerm;
            }
            else {
                echo "Search Here";
            } ?>">
        <input type="submit" name="submit" value="Search!">
    </form>
    <hr>
    <!--
        // if there is more than one result, display only the results.
        // Else, echo "no results"
    -->
    <?php if ($results->num_rows == 0): ?>
        No Stocks Found.
    <?php else: ?>
        <table>
            <tr>
                <th>Symbol</th>
                <th>Company</th>
            </tr>
        <?php while($row = $results->fetch_assoc()): ?>
            <tr>
                <td><?php echo preg_replace('/' . $searchTerm . '/i', "<strong>$0</strong>", $row["symbol"]); ?></td>
                <td><?php echo preg_replace('/' . $searchTerm . '/i', "<strong>$0</strong>", $row["companyName"]); ?></td>
            </tr>
        <?php endwhile; ?>
        </table>
    <?php endif; ?>
</body>
</html>
