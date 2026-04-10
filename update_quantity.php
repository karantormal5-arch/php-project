<?php
session_start();
include("config.php");

$user_id = $_SESSION['user_id'];

$product_id = $_GET['id'];
$type = $_GET['type'];

if($type == "plus"){

mysqli_query($conn,"
UPDATE cart 
SET quantity = quantity + 1 
WHERE product_id='$product_id' AND user_id='$user_id'
");

}

if($type == "minus"){

mysqli_query($conn,"
UPDATE cart 
SET quantity = quantity - 1 
WHERE product_id='$product_id' AND user_id='$user_id' AND quantity > 1
");

}

header("Location: cart.php");

?>