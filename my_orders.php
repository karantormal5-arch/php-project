<?php
session_start();
include("config.php");

$user_id = $_SESSION['user_id'];

$orders = mysqli_query($conn,"
SELECT * FROM orders
WHERE user_id='$user_id'
ORDER BY id DESC
");
?>

<!DOCTYPE html>
<html>
<head>

<title>My Orders</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="container mt-5">

<h2 class="mb-4">My Orders</h2>

<table class="table table-bordered">

<tr class="table-dark">
<th>Order ID</th>
<th>Products</th>
<th>Total</th>
<th>Status</th>
<th>Invoice</th>
</tr>

<?php while($order = mysqli_fetch_assoc($orders)){ ?>

<tr>

<td>#<?php echo $order['id']; ?></td>

<td>

<?php

$order_id = $order['id'];

$items = mysqli_query($conn,"
SELECT products.name, order_items.quantity
FROM order_items
JOIN products ON order_items.product_id = products.id
WHERE order_items.order_id='$order_id'
");

while($item = mysqli_fetch_assoc($items)){

echo $item['name']." (Qty ".$item['quantity'].")<br>";

}

?>

</td>

<td>₹<?php echo $order['total_amount']; ?></td>

<td>

<?php $status = $order['status']; ?>

<div style="width:250px">

<div class="progress">

<div class="progress-bar bg-warning" style="width:
<?php 
if($status=='Pending') echo '33%';
elseif($status=='Shipped') echo '66%';
else echo '100%';
?>
"></div>

</div>

<div style="font-size:12px; display:flex; justify-content:space-between;">

<span>Pending</span>
<span>Shipped</span>
<span>Delivered</span>

</div>

</div>

</td>

<!-- Invoice Button -->

<td>

<a href="invoice.php?order_id=<?php echo $order['id']; ?>" 
class="btn btn-success btn-sm">

View Bill

</a>

</td>

</tr>

<?php } ?>

</table>

<a href="index.php" class="btn btn-primary mb-3">
🏠 Home
</a>

</div>

</body>
</html>