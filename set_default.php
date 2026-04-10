<?php
session_start();
include("config.php");

$user_id = $_SESSION['user_id'];
$id = $_GET['id'];

mysqli_query($conn,"
UPDATE user_addresses SET is_default=0 WHERE user_id='$user_id'
");

mysqli_query($conn,"
UPDATE user_addresses SET is_default=1 WHERE id='$id'
");

header("Location: address.php");
?>