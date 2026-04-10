<?php
session_start();
include("config.php");

if(!isset($_SESSION['user_id'])){
    echo "Please Login";
    exit();
}

$user_id = $_SESSION['user_id'];

/* ================= CART PRODUCTS ================= */

$cart = mysqli_query($conn,"
SELECT cart.product_id, cart.quantity, products.price
FROM cart
JOIN products ON cart.product_id = products.id
WHERE cart.user_id='$user_id'
");

$total = 0;

while($row = mysqli_fetch_assoc($cart)){
    $total += $row['price'] * $row['quantity'];
}

/* ================= ACCESSORIES ================= */

$acc_total = 0;

if(isset($_SESSION['accessories'])){
    foreach($_SESSION['accessories'] as $acc_id => $qty){

        if($qty <= 0) continue;

        $q = mysqli_query($conn,"
        SELECT name, price FROM accessories WHERE id='$acc_id'
        ");

        $acc = mysqli_fetch_assoc($q);

        if(!$acc) continue;

        $acc_total += $acc['price'] * $qty;
    }
}

/* ================= FINAL TOTAL ================= */

$grand_total = $total + $acc_total;

/* ================= CREATE ORDER ================= */

mysqli_query($conn,"
INSERT INTO orders(user_id,total_amount)
VALUES('$user_id','$grand_total')
");

$order_id = mysqli_insert_id($conn);

/* ================= INSERT PRODUCTS ================= */

$cart = mysqli_query($conn,"
SELECT cart.product_id, cart.quantity, products.price
FROM cart
JOIN products ON cart.product_id = products.id
WHERE cart.user_id='$user_id'
");

while($row = mysqli_fetch_assoc($cart)){

    $product_id = $row['product_id'];
    $qty = $row['quantity'];
    $price = $row['price'];

    mysqli_query($conn,"
    INSERT INTO order_items(order_id,product_id,quantity,price)
    VALUES('$order_id','$product_id','$qty','$price')
    ");
}

/* ================= INSERT ACCESSORIES ================= */

if(isset($_SESSION['accessories'])){

    foreach($_SESSION['accessories'] as $acc_id => $qty){

        if($qty <= 0) continue;

        $q = mysqli_query($conn,"
        SELECT name, price FROM accessories WHERE id='$acc_id'
        ");

        $acc = mysqli_fetch_assoc($q);

        if(!$acc) continue;

        $name  = $acc['name'];
        $price = $acc['price'];

        mysqli_query($conn,"
        INSERT INTO order_accessories(order_id, acc_name, price, quantity)
        VALUES('$order_id','$name','$price','$qty')
        ");
    }

    // clear session after order
    unset($_SESSION['accessories']);
}

/* ================= EMPTY CART ================= */

mysqli_query($conn,"DELETE FROM cart WHERE user_id='$user_id'");

/* ================= REDIRECT ================= */

header("Location: order_success.php");
exit();
?>