<?php
session_start();
include("config.php");

$user_id = $_SESSION['user_id'];

if(isset($_POST['save'])){

$phone = $_POST['phone'];
$village = $_POST['village'];
$area = $_POST['area'];
$city = $_POST['city'];
$state = $_POST['state'];
$pincode = $_POST['pincode'];
$address = $_POST['address'];

mysqli_query($conn,"
INSERT INTO user_addresses(user_id,phone,village,area,city,state,pincode,address)
VALUES('$user_id','$phone','$village','$area','$city','$state','$pincode','$address')
");

header("Location: address.php");
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Add Address</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
background:#f2f2f2;
height:100vh;
display:flex;
align-items:center;
justify-content:center;
}

.form-card{
width:450px;
border-radius:15px;
}

</style>

</head>

<body>

<div class="card shadow form-card">

<div class="card-body">

<h3 class="text-center mb-4">  Add Address</h3>

<form method="POST">

<div class="mb-3">
<label>Phone</label>
<input type="text" name="phone" class="form-control" required>
</div>

<div class="mb-3">
<label>Village</label>
<input type="text" name="village" class="form-control" required>
</div>

<div class="mb-3">
<label>Area</label>
<input type="text" name="area" class="form-control" required>
</div>

<div class="mb-3">
<label>City</label>
<input type="text" name="city" class="form-control" required>
</div>

<div class="mb-3">
<label>State</label>
<input type="text" name="state" class="form-control" required>
</div>

<div class="mb-3">
<label>Pincode</label>
<input type="text" name="pincode" class="form-control" required>
</div>

<div class="mb-3">
<label>Full Address</label>
<textarea name="address" class="form-control" rows="3" required></textarea>
</div>

<div class="text-center">

<button name="save" class="btn btn-success">
Save Address
</button>

<a href="address.php" class="btn btn-secondary">
Cancel
</a>

</div>

</form>

</div>

</div>

</body>
</html>