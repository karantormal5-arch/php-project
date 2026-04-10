<?php
session_start();
include("config.php");

// FIX: SQL Injection se bacho — intval use karo
$id = intval($_GET['id']);

$result = mysqli_query($conn, "SELECT * FROM products WHERE id='$id'");
$row = mysqli_fetch_assoc($result);

// ── Product ki saari images fetch karo (product_images table se) ──────────
// Table structure expect: id, product_id, image_path
$img_result = mysqli_query($conn,
    "SELECT image_path FROM product_images WHERE product_id = $id ORDER BY id ASC"
);
$gallery_images = [];
while($img = mysqli_fetch_assoc($img_result)){
    $gallery_images[] = $img['image_path'];
}

// Agar koi extra image nahi hai toh sirf main image dikhao
if(empty($gallery_images)){
    $gallery_images[] = $row['image'];
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Product Details</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
body {
    background: #f4f6f9;
    font-family: 'Poppins', sans-serif;
}

/* ── Main Image ── */
.main-img-wrap {
    border-radius: 14px;
    overflow: hidden;
    background: #fff;
    border: 1px solid #e0e0e0;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 400px;
}
/* ── Thumbnails ── */
.thumb-row {
    display: flex;
    gap: 10px;
    margin-top: 14px;
    flex-wrap: wrap;
}
.thumb-item {
    width: 80px;
    height: 70px;
    border-radius: 10px;
    border: 2px solid #dee2e6;
    overflow: hidden;
    cursor: pointer;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: border-color 0.2s, transform 0.15s;
}
.thumb-item img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}
.thumb-item:hover {
    border-color: #0d6efd;
    transform: scale(1.05);
}
.thumb-item.active {
    border-color: #0d6efd;
    box-shadow: 0 0 0 3px rgba(13,110,253,0.2);
}

/* ── Angle Labels ── */
.angle-label {
    font-size: 10px;
    color: #888;
    text-align: center;
    margin-top: 4px;
    font-weight: 500;
    letter-spacing: 0.3px;
}

/* ── Product Info ── */
.product-card {
    background: #fff;
    border-radius: 14px;
    padding: 28px;
    border: 1px solid #e0e0e0;
    height: 100%;
}
.product-title {
    font-size: 26px;
    font-weight: 600;
    color: #1a1a1a;
}
.product-price {
    font-size: 28px;
    font-weight: 600;
    color: #198754;
    margin: 8px 0 16px;
}
.product-desc {
    font-size: 14px;
    color: #555;
    line-height: 1.7;
    margin-bottom: 24px;
}

/* ── Rating Section ── */
.rating-card {
    background: #fff;
    border-radius: 14px;
    padding: 22px 28px;
    border: 1px solid #e0e0e0;
    margin-top: 24px;
}

/* ── Zoom overlay on hover ── */
.main-img-wrap:hover img {
    transform: scale(1.04);
}
</style>

</head>
<body>

<!--  NAVBAR ═══════════════════════════════════════════════════════════ -->
<nav class="navbar navbar-dark bg-dark shadow-sm">
  <div class="container">
    <a href="index.php" class="navbar-brand fw-bold">My Shop</a>
    <div class="d-flex gap-2">
      <a href="cart.php"  class="btn btn-light btn-sm rounded-pill px-3">🛒 Cart</a>
      <a href="index.php" class="btn btn-light btn-sm rounded-pill px-3">🏠 Home</a>
    </div>
  </div>
</nav>


<!--  PRODUCT SECTION ------------------------------------------------- -->
<div class="container mt-5">
<div class="row g-4">

  <!-- ── LEFT: Image Gallery ── -->
  <div class="col-md-6">

    <!-- Main Image -->
    <div class="main-img-wrap">
      <img id="mainImage"
           src="images/<?php echo htmlspecialchars($gallery_images[0]); ?>"
           alt="<?php echo htmlspecialchars($row['name']); ?>">
    </div>

    <!-- Thumbnails -->
    <?php if(count($gallery_images) > 1): ?>
    <div class="thumb-row">

      <?php
      // Angle names — order ke hisaab se label lagao
      $angles = ['Front View', 'Side View', 'Rear View', 'Top View', 'Close-up'];

      foreach($gallery_images as $i => $img):
          $label = isset($angles[$i]) ? $angles[$i] : 'View ' . ($i+1);
      ?>
      <div>
        <div class="thumb-item <?php echo $i === 0 ? 'active' : ''; ?>"
             onclick="switchImage(this, 'images/<?php echo htmlspecialchars($img); ?>')">
          <img src="images/<?php echo htmlspecialchars($img); ?>"
               alt="<?php echo $label; ?>">
        </div>
        <div class="angle-label"><?php echo $label; ?></div>
      </div>
      <?php endforeach; ?>

    </div>
    <?php endif; ?>

  </div>


  <!-- ── RIGHT: Product Info ── -->
  <div class="col-md-6">
    <div class="product-card">

      <h2 class="product-title">
        <?php echo htmlspecialchars($row['name']); ?>
      </h2>

      <div class="product-price">
        ₹<?php echo htmlspecialchars($row['price']); ?>
      </div>

      <p class="product-desc">
        <?php echo htmlspecialchars($row['description']); ?>
      </p>

      <a href="add_to_cart.php?id=<?php echo intval($row['id']); ?>"
         class="btn btn-primary rounded-pill px-4 py-2 fw-500">
        🛒 Add to Cart
      </a>

    </div>
  </div>

</div>


<!-- ═══ RATING SECTION ═══════════════════════════════════════════════════ -->
<div class="rating-card">
  <form method="POST" action="rate_product.php" class="d-flex align-items-center gap-3 flex-wrap">

    <input type="hidden" name="product_id" value="<?php echo intval($row['id']); ?>">

    <label class="fw-semibold mb-0">⭐ Rate this product:</label>

    <select name="rating" class="form-select w-auto">
      <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
      <option value="4">⭐⭐⭐⭐ Good</option>
      <option value="3">⭐⭐⭐ Average</option>
      <option value="2">⭐⭐ Poor</option>
      <option value="1">⭐ Terrible</option>
    </select>

    <button type="submit" class="btn btn-warning rounded-pill px-4">
      Submit Rating
    </button>

  </form>
</div>

</div><!-- /container -->


<!-- ═══ FOOTER ════════════════════════════════════════════════════════ -->
<div class="bg-dark text-light mt-5 py-4 text-center">
    <p class="mb-0">© 2026 My Shop | Designed by Karan tormal 💻</p>
</div>


<!-- ═══ IMAGE SWITCHER JS ═════════════════════════════════════════════ -->
<script>
function switchImage(thumbEl, newSrc) {
    const mainImg = document.getElementById('mainImage');

    // Fade out
    mainImg.classList.add('fade-switch');

    setTimeout(() => {
        mainImg.src = newSrc;
        mainImg.classList.remove('fade-switch');
    }, 220);

    // Active thumbnail highlight
    document.querySelectorAll('.thumb-item').forEach(t => t.classList.remove('active'));
    thumbEl.classList.add('active');
}
</script>

</body>
</html>