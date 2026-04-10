<?php
session_start();
include("config.php");

$user_id = $_SESSION['user_id'];
$product_id = $_GET['id'];

mysqli_query($conn,"
DELETE FROM wishlist
WHERE user_id='$user_id' AND product_id='$product_id'
");

header("Location: wishlist.php");
?>