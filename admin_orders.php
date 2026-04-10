<?php
include("config.php");

$result = mysqli_query($conn,"
SELECT orders.*, users.name
FROM orders
JOIN users ON orders.user_id = users.id
");
?>

<h2>All Orders</h2>

<table border="1">

<tr>
<th>Order ID</th>
<th>Customer</th>
<th>Total</th>
<th>Payment</th>
</tr>

<?php while($row = mysqli_fetch_assoc($result)){ ?>

<tr>

<td><?php echo $row['id']; ?></td>
<td><?php echo $row['name']; ?></td>
<td>₹<?php echo $row['total_amount']; ?></td>
<td><?php echo $row['payment_method']; ?></td>

</tr>

<?php } ?>

</table>