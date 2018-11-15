<?php

    include "databaseConnection.php";

    $sql_investments = "SELECT * FROM Investments ORDER BY i_username;";
    $sql_users = "SELECT * FROM Users ORDER BY u_username;";
    $sql_rankings = "SELECT * FROM Rankings ORDER BY r_username;";

    $results_investments = $mysqli->query($sql_investments);
    $results_users = $mysqli->query($sql_users);
    $results_rankings = $mysqli->query($sql_rankings);

    if(!$results_users || !$results_investments || $results_rankings) {
        echo json_encode("Error while retrieving user/investment info: " . $mysqli->connect_errno);
        exit();
    }

    $user_array = [];
    $rankings_array = [];

    while($row_user = $results_users->fetch_assoc()) {
        $user_array[$row_user['u_username']] = $row_user['u_balance'];
    }
    while($row_rank = $results_rankings->fetch_assoc()) {
        $rankings_array[$row_rank['r_username']] = $row_rank['r_ranking'];
    }

    $arrContextOptions=array(
        "ssl"=>array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
        ),
    );

    while($row_investment = $results_investments->fetch_assoc()) {
        $price = file_get_contents("https://api.iextrading.com/1.0/stock/".urlencode($row_investment['i_symbol'])."/price", false, stream_context_create($arrContextOptions));
        $price = (int) (((float) $price) * 100.0);

        if($price > 0) {
            $profit = $price * $row_investment['i_amount'];
        } else {
            $profit = 0;
        }

        $user_array[$row_investment['i_username']] += $profit;
    }

    foreach ($user_array as $key => $value) {
        $sql = "UPDATE Rankings SET r_money = " . $value . " WHERE r_ranking = " . $results_rankings[$key] . ";";

        if($mysqli->query($sql) === FALSE) {
            echo json_encode("Error updating rankings for $key and $value: " . $mysqli->connect_errno);
            exit();
        }
    }

    echo json_encode("Successful Updates");

?>
