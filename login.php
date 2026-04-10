
<?php
session_start();
include("config.php");

if(isset($_POST['login'])){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = mysqli_query($conn,
    "SELECT * FROM users WHERE email='$email' AND password='$password'");

    if(mysqli_num_rows($result) > 0){
        $row = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $row['id'];
        header("Location: index.php");
    } else {
        $error = "Invalid Email or Password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container">
<div class="row justify-content-center mt-5">
<div class="col-md-4">

<div class="card shadow">
<div class="card-body">

<h3 class="text-center mb-4">Login</h3>

<?php if(isset($error)){ ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php } ?>

<form method="post">
  <div class="mb-3">
    <input type="email" name="email" class="form-control" placeholder="Enter Email" required>
  </div>

  <div class="mb-3">
    <input type="password" name="password" class="form-control" placeholder="Enter Password" required>
  </div>

  <button name="login" class="btn btn-primary w-100">Login</button>
</form>

<div class="text-center mt-3">
  <a href="register.php">Don't have account? Register</a>
</div>

</div>
</div>

</div>
</div>
</div>

</body>
</html>