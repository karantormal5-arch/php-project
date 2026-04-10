<?php
session_start();
include("config.php");

if(!isset($_SESSION['user_id'])){
echo "Please Login";
exit();
}

$order_id = $_GET['order_id'];

/* Order Info */

$order = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT * FROM orders WHERE id='$order_id'
"));

/* Order Items */

$items = mysqli_query($conn,"
SELECT products.name, order_items.quantity, order_items.price
FROM order_items
JOIN products ON order_items.product_id = products.id
WHERE order_items.order_id='$order_id'
");
?>

<!DOCTYPE html>
<html>
<head>

<title>Order Details</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="container mt-5">

<h2>Order #<?php echo $order['id']; ?></h2>

<p><b>Status:</b> <?php echo $order['status']; ?></p>

<p><b>Total Amount:</b> ₹<?php echo $order['total_amount']; ?></p>

<hr>

<h4>Products</h4>

<table class="table table-bordered">

<tr class="table-dark">
<th>Product</th>
<th>Qty</th>
<th>Price</th>
</tr>

<?php while($row = mysqli_fetch_assoc($items)){ ?>

<tr>

<td><?php echo $row['name']; ?></td>

<td><?php echo $row['quantity']; ?></td>

<td>₹<?php echo $row['price']; ?></td>

</tr>

<?php } ?>

</table>

</div>

</body>
</html>
<a href="invoice.php?order_id=<?php echo $order['id']; ?>" 
class="btn btn-success">
Download Invoice
</a>