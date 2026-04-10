<?php
session_start();
include("config.php");

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'];
$rating = $_POST['rating'];

/* check if already rated */

$check = mysqli_query($conn,"
SELECT * FROM ratings 
WHERE user_id='$user_id' AND product_id='$product_id'
");

if(mysqli_num_rows($check) == 0){

mysqli_query($conn,"
INSERT INTO ratings(user_id,product_id,rating)
VALUES('$user_id','$product_id','$rating')
");

}else{

mysqli_query($conn,"
UPDATE ratings 
SET rating='$rating'
WHERE user_id='$user_id' AND product_id='$product_id'
");

}

header("Location: product.php?id=$product_id");
exit();
?>