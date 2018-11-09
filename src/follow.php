<?php
    session_start();
    
    if (!isset($_SESSION) || !array_key_exists("signedIn", $_SESSION) || $_SESSION["signedIn"] === FALSE) {
        header('Location: signin.php');
        exit();
    }
    
    include 'databaseConnection.php';
    
    $symbol = urldecode($_REQUEST["symbol"]);
    $escapedSymbol = mysqli_real_escape_string($mysqli, $symbol);
    
    $username = $_SESSION["username"];
    
    $sql = "SELECT * FROM Investments WHERE i_symbol=\"" . $escapedSymbol . "\"AND i_username=\"" . $username ."\";";
    $results = $mysqli->query($sql);
    if (!$results) {
        echo "SQL ERROR while fetching investment info: " . $mysqli->error;
        exit();
    }
    
    if ($results->num_rows == 0) {
        $updateSql = "INSERT INTO Investments(i_username, i_symbol, i_amount) VALUES " .
                    "(\"$username\", \"$escapedSymbol\", 0);";
        if ($mysqli->query($updateSql)) {
            echo "Followed";
            $mysqli->close();
            exit();
        }
    }
    else {
        $row = $results->fetch_assoc();
        if ($row["i_amount"] == 0) {
            $updateSql = "DELETE FROM Investments WHERE i_symbol=\"" . $escapedSymbol . "\" AND i_username=\"" . $username . "\";";
            if ($mysqli->query($updateSql)) {
                echo "Unfollowed";
                $mysqli->close();
                exit();
            }
        }
        else {
            echo "amountNotZero";
            $mysqli->close();
            exit();
        }
    }
?>
