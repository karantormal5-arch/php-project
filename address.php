<?php
session_start();
include("config.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$result = mysqli_query($conn,"
SELECT * FROM user_addresses 
WHERE user_id='$user_id'
ORDER BY id DESC
LIMIT 1
");

$address = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>

<title>My Address</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
background:#f4f6f9;
height:100vh;
display:flex;
align-items:center;
justify-content:center;
}

.address-card{
width:420px;
border-radius:15px;
}

</style>

</head>

<body>

<div class="card shadow address-card">

<div class="card-body text-center">

<h3 class="mb-4">📍 My Address</h3>

<?php if($address){ ?>

<p><b>Phone:</b> <?php echo $address['phone']; ?></p>

<p><b>Village:</b> <?php echo $address['village']; ?></p>

<p><b>Area:</b> <?php echo $address['area']; ?></p>

<p><b>City:</b> <?php echo $address['city']; ?></p>

<p><b>State:</b> <?php echo $address['state']; ?></p>

<p><b>Pincode:</b> <?php echo $address['pincode']; ?></p>

<p><b>Address:</b><br>
<?php echo $address['address']; ?>
</p>

<a href="add_address.php" class="btn btn-warning mt-3">
Edit Address
</a>

<?php } else { ?>

<p>No Address Added</p>

<a href="add_address.php" class="btn btn-primary mt-3">
Add Address
</a>

<?php } ?>

<br>

<a href="index.php" class="btn btn-dark mt-3">
home
</a>

</div>

</div>

</body>
</html>