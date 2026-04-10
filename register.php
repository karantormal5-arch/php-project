<?php
include("config.php");

if(isset($_POST['register'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    mysqli_query($conn,"INSERT INTO users(name,email,password)
    VALUES('$name','$email','$password')");

    $success = "Registration Successful!";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Register</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container">
<div class="row justify-content-center mt-5">
<div class="col-md-4">

<div class="card shadow">
<div class="card-body">

<h3 class="text-center mb-4">Register</h3>

<?php if(isset($success)){ ?>
<div class="alert alert-success"><?php echo $success; ?></div>
<?php } ?>

<form method="post">

  <div class="mb-3">
    <input type="text" name="name" class="form-control" placeholder="Enter Name" required>
  </div>

  <div class="mb-3">
    <input type="email" name="email" class="form-control" placeholder="Enter Email" required>
  </div>

  <div class="mb-3">
    <input type="password" name="password" class="form-control" placeholder="Enter Password" required>
  </div>

  <button name="register" class="btn btn-success w-100">Register</button>

</form>

<div class="text-center mt-3">
  <a href="login.php">Already have account? Login</a>
</div>

</div>
</div>

</div>
</div>
</div>

</body>
</html>