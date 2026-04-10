<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit; // IMPORTANT: exit lagana zaroori hai
}
include("config.php");

// ─── Category aur Search handle karo ───────────────────────────────────────
$where = "WHERE 1=1";

if(isset($_GET['cat']) && intval($_GET['cat']) > 0){
    $cat_id = intval($_GET['cat']); // Safe integer
    $where .= " AND p.category_id = $cat_id";
}

if(isset($_GET['search']) && $_GET['search'] != ""){
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where .= " AND p.name LIKE '%$search%'";
}

// Ek hi query mein products + avg rating (N+1 fix)
$result = mysqli_query($conn,
    "SELECT p.*, ROUND(AVG(r.rating)) as avg_rating
     FROM products p
     LEFT JOIN ratings r ON r.product_id = p.id
     $where
     GROUP BY p.id"
);

$active_cat = isset($_GET['cat']) ? intval($_GET['cat']) : 0;
?>

<!DOCTYPE html>
<html>
<head>
<title>E-Commerce</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
    font-family: 'Poppins', sans-serif;
    transition: 0.4s;
}

/* ── Dark Mode ─────────────────────────────────────────── */
.dark-mode{
    background-color: #000 !important;
    color: white !important;
}
.dark-mode .card{
    background-color: #111;
    color: white;
}
.dark-mode .navbar{
    background-color: #000 !important;
}
.dark-mode .form-control{
    background-color: #222;
    color: white;
    border: 1px solid #444;
}
.dark-mode .cat-btn.active, .dark-mode .cat-btn:hover{
    background-color: #fff;
    color: #000;
}
.dark-mode .cat-btn{
    color: #ccc;
    border-color: #555;
}

/* FIX 1: Dark mode mein description text visible karo */
.dark-mode .text-muted{
    color: #adb5bd !important;   /* Bootstrap ka muted — dark bg pe readable grey */
}

.product-img{
    height: 220px;
    object-fit: cover;
}

/* Category Buttons */
.cat-btn{
    border-radius: 50px;
    padding: 8px 22px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    border: 2px solid #dee2e6;
    color: #555;
    background: white;
    transition: 0.2s;
}
.cat-btn:hover, .cat-btn.active{
    background-color: #0d6efd;
    color: white;
    border-color: #0d6efd;
}

/* Wishlist icon */
.wishlist-icon{
    position: absolute;
    top: 10px;
    right: 10px;
    background: white;
    border-radius: 50%;
    padding: 6px 10px;
    font-size: 18px;
    color: red;
    text-decoration: none;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    z-index: 1;
}
.card{
    position: relative;
}

/* FIX 2: Card body flex layout — button hamesha neeche fixed rahega */
.card-body{
    display: flex;
    flex-direction: column;
}
.card-body .btn-view{
    margin-top: auto;   /* baki content ke baad button neeche push ho jaata hai */
}
</style>

</head>
<body id="body">


<!-- ═══════════════════════════ NAVBAR ═══════════════════════════════════ -->

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">
  <div class="container">

    <a class="navbar-brand" href="index.php">My Shop</a>

    <form class="d-flex" method="GET" action="index.php">
      <!-- Category ko search ke saath pass karo -->
      <?php if($active_cat > 0): ?>
      <input type="hidden" name="cat" value="<?php echo $active_cat; ?>">
      <?php endif; ?>

      <input class="form-control me-2"
             type="search"
             name="search"
             placeholder="Search product..."
             value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">

      <button class="btn btn-light" type="submit">Search</button>
    </form>

    <button onclick="toggleDarkMode()" class="btn btn-warning ms-3">🌙</button>

    <a href="logout.php" class="btn btn-danger ms-2 rounded-pill">Logout</a>
    <a href="my_account.php" class="btn btn-outline-light ms-2 rounded-pill px-3">👤 My Account</a>

  </div>
</nav>


<!-- ═══════════════════════════ CATEGORY BUTTONS ═════════════════════════ -->

<div class="container mt-4">
  <div class="d-flex gap-2 flex-wrap justify-content-center">

    <!-- All Products -->
    <a href="index.php"
       class="cat-btn <?php echo $active_cat == 0 ? 'active' : ''; ?>">
      🛒 All
    </a>

    <!-- Bike -->
    <a href="index.php?cat=1"
       class="cat-btn <?php echo $active_cat == 1 ? 'active' : ''; ?>">
      🏍️ Bike
    </a>

    <!-- Scooty -->
    <a href="index.php?cat=2"
       class="cat-btn <?php echo $active_cat == 2 ? 'active' : ''; ?>">
      🛵 Scooty
    </a>

    <!-- EV Scooty -->
    <a href="index.php?cat=3"
       class="cat-btn <?php echo $active_cat == 3 ? 'active' : ''; ?>">
      ⚡ EV Scooty
    </a>

  </div>
</div>


<!-- ═══════════════════════════ PRODUCTS ════════════════════════════════ -->

<div class="container mt-4">

  <!-- Koi product nahi mila toh message dikhao -->
  <?php if(mysqli_num_rows($result) == 0): ?>
  <div class="text-center py-5">
    <h4>  product dosn't found....</h4>
    <p class="text-muted">tray other category or search.....</p>
    <a href="index.php" class="btn btn-primary rounded-pill px-4">see all</a>
  </div>
  <?php endif; ?>

  <div class="row">

  <?php while($row = mysqli_fetch_assoc($result)){ 
      $avg = intval($row['avg_rating']); // Already JOIN se aa raha hai
  ?>

  <div class="col-md-4 mb-4">
    <div class="card shadow h-100">

      <!-- Wishlist -->
      <a href="add_wishlist.php?product_id=<?php echo intval($row['id']); ?>"
         class="wishlist-icon">❤️</a>

      <!-- Image -->
      <img src="images/<?php echo htmlspecialchars($row['image']); ?>"
           class="card-img-top product-img"
           alt="<?php echo htmlspecialchars($row['name']); ?>">

      <div class="card-body text-center">

        <!-- Name -->
        <h5 class="card-title">
          <a href="product.php?id=<?php echo intval($row['id']); ?>"
             style="text-decoration:none; color:inherit;">
            <?php echo htmlspecialchars($row['name']); ?>
          </a>
        </h5>

        <!-- Price -->
        <h5 class="text-success fw-bold">
          ₹<?php echo htmlspecialchars($row['price']); ?>
        </h5>

        <!-- Star Rating -->
        <div class="mb-2">
          <?php for($i = 1; $i <= 5; $i++){ echo $i <= $avg ? "⭐" : "☆"; } ?>
        </div>

        <!-- Description -->
        <p class="text-muted small">
          <?php echo htmlspecialchars($row['description']); ?>
        </p>

        <!-- FIX 2: btn-view class laga di — hamesha card ke bilkul neeche rahega -->
        <a href="product.php?id=<?php echo intval($row['id']); ?>"
           class="btn btn-dark rounded-pill px-4 btn-view">
           View Details
        </a>

      </div>
    </div>
  </div>

  <?php } ?>

  </div>
</div>


<!-- ═══════════════════════════ FOOTER ══════════════════════════════════ -->

<div class="bg-dark text-light mt-5 py-4 text-center">
    <h5>My Shop</h5>
    <p>Best Online Shopping Website</p>
    <p class="mb-0">© 2026 All Rights Reserved | Designed by Karan tormal 💻</p>
</div>


<!-- ═══════════════════════════ DARK MODE JS ═════════════════════════════ -->

<script>
window.onload = function() {
    if(localStorage.getItem("darkMode") === "enabled"){
        document.getElementById("body").classList.add("dark-mode");
    }
}

function toggleDarkMode() {
    let body = document.getElementById("body");
    body.classList.toggle("dark-mode");
    if(body.classList.contains("dark-mode")){
        localStorage.setItem("darkMode", "enabled");
    } else {
        localStorage.setItem("darkMode", "disabled");
    }
}
</script>

</body>
</html>