<!-- Basic searching implemented.
Search result will be displayed in alphabetical order of symbols.
The keywords will be bolded in the search result
Showing a blurb when clicked is not yet implemented-->
<?php
    session_start();

    include 'databaseConnection.php';

    if (array_key_exists("searchTerm", $_REQUEST)) {
        $searchTerm = $_REQUEST["searchTerm"];
        $urlEncodedTerm = urlencode($searchTerm);
        $escapedTerm = mysqli_real_escape_string($mysqli, $searchTerm);
        $sql = "SELECT * FROM Stocks WHERE companyName LIKE '%" . $escapedTerm . "%' OR symbol LIKE '%" . $escapedTerm . "%' LIMIT 20;";
    }
    else
    {
        $searchTerm = "";
        $urlEncodedTerm = "";
        $sql = "SELECT * FROM Stocks LIMIT 20;";
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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script>
        jQuery(document).ready(function($) {
            $(".clickable-row").click(function() {
                window.location = $(this).data("href");
            });
        });
    </script>
</head>
<body>
    <form name="searchForm" action="search.php">
        <input type="text" name="searchTerm" placeholder="Search..."
            value="<?php if ($searchTerm) {
                echo $searchTerm;
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
            <tr class='clickable-row' data-href='blurb.php?symbol=<?php echo urlencode($row["symbol"]);?>'>
                <td><?php echo preg_replace('/' . $urlEncodedTerm . '/i', "<strong>$0</strong>", $row["symbol"]); ?></td>
                <td><?php echo preg_replace('/' . $urlEncodedTerm . '/i', "<strong>$0</strong>", $row["companyName"]); ?></td>
            </tr>
        <?php endwhile; ?>
        </table>
    <?php endif; ?>
</body>
</html>
