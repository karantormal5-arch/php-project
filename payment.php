<?php
session_start();
include("config.php");

$user_id = $_SESSION['user_id'];

$result = mysqli_query($conn,"
SELECT products.name, products.price, cart.quantity
FROM cart
JOIN products ON cart.product_id = products.id
WHERE cart.user_id='$user_id'
");

$total = 0;
?>

<!DOCTYPE html>
<html>
<head>

<title>Payment</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5">

<h2 class="text-center mb-4">Checkout</h2>

<table class="table table-bordered text-center">

<tr class="table-dark">
<th>Product</th>
<th>Price</th>
<th>Qty</th>
<th>Total</th>
</tr>

<?php while($row = mysqli_fetch_assoc($result)){ 

$item_total = $row['price'] * $row['quantity'];
$total += $item_total;

?>

<tr>
<td><?php echo $row['name']; ?></td>
<td>₹<?php echo $row['price']; ?></td>
<td><?php echo $row['quantity']; ?></td>
<td>₹<?php echo $item_total; ?></td>
</tr>

<?php } ?>

</table>

<h4 class="text-end">Total Amount : ₹<?php echo $total; ?></h4>

<form action="place_order.php" method="POST">

<input type="hidden" name="total" value="<?php echo $total; ?>">

<div class="mt-4">

<h5>Select Payment Method</h5>

<input type="radio" name="payment" value="UPI" required> UPI  
<br>

<input type="radio" name="payment" value="COD"> Cash on Delivery  

</div>

<div class="text-center mt-4">

<button class="btn btn-success btn-lg">
Place Order
</button>

</div>

</form>

</div>

</body>
</html>