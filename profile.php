<?php
session_start();
include("config.php");

$user_id = $_SESSION['user_id'];

$result = mysqli_query($conn,"SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
<title>My Profile</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
</style>

</head>

<body>

<div class="container">

<div class="card shadow profile-card">

<div class="card-body text-center">

<h2 class="mb-4">👤 My Profile</h2>

<h5>
<strong>Name:</strong> <?php echo $user['name']; ?>
</h5>

<h5 class="mt-3">
<strong>Email:</strong> <?php echo $user['email']; ?>
</h5>

<a href="index.php" class="btn btn-primary mt-4">
Home
</a>

<a href="my_account.php" class="btn btn-primary mt-4">
back
</a>

</div>

</div>

</div>

</body>
</html>