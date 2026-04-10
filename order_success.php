<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>

<title>Order Success</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
background:#f1f3f6;
height:100vh;
display:flex;
align-items:center;
justify-content:center;
}

.success-card{
width:450px;
border-radius:15px;
text-align:center;
}

.success-icon{
font-size:60px;
color:green;
}

</style>

</head>

<body>

<div class="card shadow p-4 success-card">

<div class="success-icon">
✅
</div>

<h3 class="mt-3">Order Placed Successfully</h3>

<p class="text-muted">
Thank you for shopping with us.
Your order has been placed successfully.
</p>

<div class="mt-4">

<a href="index.php" class="btn btn-primary">
Continue Shopping
</a>

<a href="my_orders.php" class="btn btn-success">
View My Orders
</a>

</div>

</div>

</body>
</html>