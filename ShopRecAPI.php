<?php
session_start();

require 'SQLconn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($Connection)) {
        $Connection = new PDOConnect("DPD_DB");
    }
    if (isset($_POST['SQL_Select'])) {
        $stmt = $Connection->select($_POST['SQL_Select']);
        echo json_encode($stmt);
    }
}
?>
