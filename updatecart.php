<?php
session_start();
include("config.php");
echo $_SESSION['user_id'];

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if(isset($_GET['id']) && isset($_GET['type'])){

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
SET quantity = quantity + 1 
WHERE product_id='$product_id' AND user_id='$user_id'
") or die(mysqli_error($conn));

}

}

header("Location: cart.php");
exit();

?>