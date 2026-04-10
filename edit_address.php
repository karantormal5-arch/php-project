<?php
session_start();
include("config.php");

$id = $_GET['id'];

$result = mysqli_query($conn,"
SELECT * FROM user_addresses WHERE id='$id'
");

$row = mysqli_fetch_assoc($result);

if(isset($_POST['update'])){

$phone = $_POST['phone'];
$address = $_POST['address'];

mysqli_query($conn,"
UPDATE user_addresses
SET phone='$phone',address='$address'
WHERE id='$id'
");

header("Location: address.php");

}
?>

<h2>Edit Address</h2>

<form method="POST">

<input type="text" name="phone" value="<?php echo $row['phone']; ?>">

<br><br>

<textarea name="address"><?php echo $row['address']; ?></textarea>

<br><br>

<button name="update">Update</button>

</form>