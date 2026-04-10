<?php
session_start();
include("config.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$result = mysqli_query($conn,"SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>

<head>

<title>My Account</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


</style>

</head>

<body>

<div class="container mt-5">

<h2 class="mb-4 text-center">My Account</h2>

<div class="text-center mb-5">

<h4><?php echo $user['name']; ?></h4>
<p><?php echo $user['email']; ?></p>

</div>

<div class="row justify-content-center">


<!-- Profile -->

<div class="col-md-3 mb-4">

<div class="card account-card text-center p-4">

<i class="fas fa-user icon"></i>

<h5>Profile</h5>

<a href="profile.php" class="btn btn-outline-primary btn-sm mt-2">
View
</a>

</div>

</div>


<!-- Orders -->

<div class="col-md-3 mb-4">

<div class="card account-card text-center p-4">

<i class="fas fa-box icon"></i>

<h5>Orders</h5>

<a href="my_orders.php" class="btn btn-outline-primary btn-sm mt-2">
View
</a>

</div>

</div>


<!-- Address -->

<div class="col-md-3 mb-4">

<div class="card account-card text-center p-4">

<i class="fas fa-map-marker-alt icon"></i>

<h5>Address</h5>

<a href="address.php" class="btn btn-outline-primary btn-sm mt-2">
Manage
</a>

</div>

</div>


<!-- Wishlist -->

<div class="col-md-3 mb-4">

<div class="card account-card text-center p-4">

<i class="fas fa-heart icon"></i>

<h5>Wishlist</h5>

<a href="wishlist.php" class="btn btn-outline-primary btn-sm mt-2">
View
</a>

</div>

</div>


<!-- Logout -->

<div class="col-md-3 mb-4">

<div class="card account-card text-center p-4">

<i class="fas fa-sign-out-alt icon text-danger"></i>

<h5>Logout</h5>

<a href="logout.php" class="btn btn-danger btn-sm mt-2">
Logout
</a>

</div>

</div>
<a href="index.php" class="btn btn-primary mb-3">
back
</a>

</div>

</div>

</body>
</html>