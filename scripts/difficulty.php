<?php
require "db.php";
require "api_call.php";
require "api_logging.php";
require_once('easybitcoin.php');
$stmt = $dbh->query("SELECT * FROM coins");
while ($result = $stmt->fetch(PDO::FETCH_LAZY)) {
    $rpc_mode = $result->rpc;
    if ($rpc_mode == 0) {
        $url = $result->url;
        $data = apiCall($url);
        $info = json_decode($data, true);
        if (!$data || json_last_error() !== JSON_ERROR_NONE) {
            $text_error = 'Ошибка API монеты ' . $result->code . ' по адресу: ' . $url;
            api_error($text_error);
            continue;
        } else {
            $coin[] = $result->code;
            $parameter = $result->parameter;
            $addition = trim($result->addition);
            if ($addition != NULL && $parameter != NULL) {
                $additions = explode(" ", $addition);
                $num_additions = count($additions);
                if ($num_additions == 1) {
                    $difficulty[] = $info[$additions[0]][$parameter];
                } elseif ($num_additions == 2) {
                    $difficulty[] = $info[$additions[0]][$additions[1]][$parameter];
                } elseif ($num_additions == 3) {
                    $difficulty[] = $info[$additions[0]][$additions[1]][$additions[2]][$parameter];
                } elseif ($num_additions == 4) {
                    $difficulty[] = $info[$additions[0]][$additions[1]][$additions[2]][$additions[3]][$parameter];
                } elseif ($num_additions == 5) {
                    $difficulty[] = $info[$additions[0]][$additions[1]][$additions[2]][$additions[3]][$additions[4]][$parameter];
                }
            } elseif ($addition == NULL && $parameter != NULL) {
                $difficulty[] = $info[$parameter];
            } elseif ($addition == NULL && $parameter == NULL) {
                $difficulty[] = $info;
            }
        }
    } else {
        $coin[] = $result->code;
        $rpcuser = $result->rpcuser;
        $rpcpassword = $result->rpcpassword;
        $rpcport = $result->rpcport;
        $rpc_method = $result->rpc_method;
        $rpc_parameter = $result->rpc_parameter;
        $rpc = new Bitcoin($rpcuser, $rpcpassword, 'localhost', $rpcport);
        if ($rpc_method == NULL && $rpc_parameter == NULL) {
            $rpc->getinfo();
            $difficulty[] = $rpc->response['result']['difficulty'];
        } elseif ($rpc_method != NULL && $rpc_parameter != NULL) {
            $rpc->$rpc_method();
            $difficulty[] = $rpc->response['result'][$rpc_parameter];
        } elseif ($rpc_method != NULL && $rpc_parameter == NULL) {
            $rpc->$rpc_method();
            $difficulty[] = $rpc->response['result']['difficulty'];
        } elseif ($rpc_method == NULL && $rpc_parameter != NULL) {
            $rpc->getinfo();
            $difficulty[] = $rpc->response['result'][$rpc_parameter];
        }
    }

}
$coins = implode(', ', $coin);
$difficulties = join(', ', $difficulty);
$query = "INSERT INTO difficulty (datetime, $coins) VALUES (NOW(), $difficulties)";
$dbh->query($query);
?>