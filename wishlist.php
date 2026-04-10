<?php
session_start();
include("config.php");

$user_id = $_SESSION['user_id'];

$result = mysqli_query($conn,"
SELECT products.*
FROM wishlist
JOIN products ON wishlist.product_id = products.id
WHERE wishlist.user_id='$user_id'
");
?>

<!DOCTYPE html>
<html>

<head>

<title>My Wishlist</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="container mt-5">

<h2>My Wishlist</h2>

<div class="row">

<?php while($row = mysqli_fetch_assoc($result)){ ?>

<div class="col-md-3">

<div class="card p-3 mb-4">

<h5><?php echo $row['name']; ?></h5>

<p>₹<?php echo $row['price']; ?></p>

<a href="add_to_cart.php?id=<?php echo $row['id']; ?>" 
class="btn btn-success btn-sm">

Add to Cart

</a>

<a href="remove_wishlist.php?id=<?php echo $row['id']; ?>" 
class="btn btn-danger btn-sm">

Remove

</a>

</div>

</div>

<?php } ?>

</div>
<a href="index.php" class="btn btn-primary mb-3">
🏠 Home
</a>
</div>

</body>
</html>