<?php
require "db.php";
$asic_name=$_POST['asic_name'];
$tdp = $_POST['tdp'];
$algos = array_slice($_POST, 2);
foreach ($algos as $algo=>$value) {
    if ($value !=NULL) {
        $new_array_algo[]=$algo;
        $new_array_hashes[]=$value;
    }
}
$algos=implode(', ', $new_array_algo);
$hashes=implode(', ', $new_array_hashes);
$stmt = $dbh->query("INSERT INTO ASIC (name, tdp, $algos) VALUES ('$asic_name', $tdp, $hashes)");
