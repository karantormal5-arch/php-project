<?php
session_start();
include("config.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = $_GET['product_id'];

mysqli_query($conn,"
INSERT INTO wishlist(user_id,product_id)
VALUES('$user_id','$product_id')
");

header("Location: wishlist.php");
exit();
?>