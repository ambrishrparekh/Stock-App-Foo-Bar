<?php
session_start();

include 'databaseConnection.php';

$symbol = urldecode($_REQUEST["symbol"]);
$escapedSymbol = mysqli_real_escape_string($mysqli, $symbol);

// Retrieves information about the stock from Stocks table
$infoSql = "SELECT * FROM Stocks WHERE symbol=\"$escapedSymbol\";";
$infoResults = $mysqli->query($infoSql);
    echo "SQL ERROR: " . $mysqli->error;
    exit();
}
$infoRow = $infoResults->fetch_assoc();
?>

Symbol: <?php echo $infoRow["symbol"];?><br />
Company: <?php echo $infoRow["companyName"];?><br />
Description: <?php echo $infoRow["companyDescription"];?><br />
CEO: <?php echo $infoRow["CEO"];?><br />
Industry: <?php echo $infoRow["industry"];?><br />
Exchange: <?php echo $infoRow["market"];?><br />
<hr>

<?php 
if (!isset($_SESSION) || !array_key_exists("signedIn", $_SESSION) || $_SESSION["signedIn"] === FALSE) {
    echo "Sign in to invest in this stock";
}
else {
    $username = $_SESSION["username"];
    
    $sql = "SELECT * FROM Investments WHERE i_symbol=\"" . $escapedSymbol . "\"AND i_username=\"" . $username ."\";";
    $results = $mysqli->query($sql);
    if (!$results) {
        echo "SQL ERROR while fetching investment info: " . $mysqli->error;
        exit();
    }

    echo "<button id=\"followButton\" name=\"follow\" onClick=\"follow()\">";
    if ($results->num_rows == 0) {
        echo "Follow";
    }
    else {
        echo "Unfollow";
    }
    echo "</button>";
}
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>
function follow() {
		$.ajax({
			url: "follow.php",
			data: {
				symbol: "<?php echo $_REQUEST["symbol"];?>"
			},
			success: function(result) {
				console.log(result);
				if (result == "Followed") {
					$("#followButton").html("Unfollow");
					confirm("Stock Followed");
				}
				else if (result == "Unfollowed") {
					$("#followButton").html("Follow");
					confirm("Stock Unfollowed");
				}
				else if (result == "amountNotZero") {
					confirm("You cannot unfollow stocks that you possess");
				}
				else {
					confirm("There was an error");
				}
			}
		});
	};
</script>

