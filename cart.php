<?php
session_start();
include("config.php");

$user_id = $_SESSION['user_id'];

/* ── ACCESSORIES: Session mein qty store karo ─────────────────────────── */
// acc[] = [ acc_id => quantity, ... ]

// + button
if(isset($_GET['acc_plus'])){
    $acc_id = intval($_GET['acc_plus']);
    if(!isset($_SESSION['accessories'][$acc_id])){
        $_SESSION['accessories'][$acc_id] = 0;
    }
    $_SESSION['accessories'][$acc_id]++;
    header("Location: cart.php"); exit();
}

// - button
if(isset($_GET['acc_minus'])){
    $acc_id = intval($_GET['acc_minus']);
    if(isset($_SESSION['accessories'][$acc_id])){
        $_SESSION['accessories'][$acc_id]--;
        if($_SESSION['accessories'][$acc_id] <= 0){
            unset($_SESSION['accessories'][$acc_id]);
        }
    }
    header("Location: cart.php"); exit();
}

/* ── Cart products fetch ──────────────────────────────────────────────── */
// selected_color column check karo — nahi hai toh bina uske query karo
$has_color = mysqli_num_rows(mysqli_query($conn, "SHOW COLUMNS FROM cart LIKE 'selected_color'")) > 0;
$color_col = $has_color ? ", cart.selected_color" : "";

$result = mysqli_query($conn,"
    SELECT products.name, products.price, cart.quantity, cart.product_id $color_col
    FROM cart
    JOIN products ON cart.product_id = products.id
    WHERE cart.user_id = '$user_id'
");

$total     = 0;
$acc_total = 0;
$GST_RATE  = 18; // 18% fixed GST
?>

<!DOCTYPE html>
<html>
<head>
<title>Your Cart</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
body { font-family: 'Poppins', sans-serif; background: #f4f6f9; }

/* ── Accessory Card ── */
.acc-card {
    border: 1.5px solid #dee2e6;
    border-radius: 12px;
    padding: 10px 8px;
    background: #fff;
    text-align: center;
    transition: border-color 0.2s, box-shadow 0.2s;
    position: relative;
}
.acc-card.selected {
    border-color: #0d6efd;
    box-shadow: 0 0 0 3px rgba(13,110,253,0.15);
}
.acc-img {
    height: 85px;
    object-fit: contain;
    margin-bottom: 8px;
}
.acc-name {
    font-size: 13px;
    font-weight: 500;
    margin-bottom: 2px;
}
.acc-price {
    font-size: 12px;
    color: #198754;
    font-weight: 600;
    margin-bottom: 8px;
}

/* ── Qty Controls ── */
.qty-controls {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.qty-btn {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    border: none;
    font-size: 16px;
    font-weight: 600;
    line-height: 1;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: transform 0.1s;
}
.qty-btn:hover { transform: scale(1.15); }
.qty-btn.minus { background: #dc3545; color: #fff; }
.qty-btn.plus  { background: #198754; color: #fff; }
.qty-num {
    font-size: 15px;
    font-weight: 600;
    min-width: 20px;
    text-align: center;
    color: #1a1a1a;
}

/* ── Selected Accessories List ── */
.acc-list-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #e0e0e0;
    font-size: 14px;
}
.acc-list-row:last-child { border-bottom: none; }

/* ── Dark Mode ── */
.dark-mode { background: #000 !important; color: #f0f0f0 !important; }
.dark-mode .acc-card { background: #111; border-color: #333; color: #f0f0f0; }
.dark-mode .acc-card.selected { border-color: #0d6efd; }
.dark-mode .table { color: #f0f0f0; }
.dark-mode .table-dark thead { background: #000; }
.dark-mode .card-summary { background: #111 !important; border-color: #333; color: #f0f0f0; }
.dark-mode .qty-num { color: #fff; }
.dark-mode .navbar { background-color: #000 !important; }
</style>
</head>

<body class="bg-light" id="body">

<!-- ═══ NAVBAR ══════════════════════════════════════════════════════════ -->
<nav class="navbar navbar-dark bg-dark shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">My Shop</a>
    <div class="d-flex gap-2">
      <button onclick="toggleDarkMode()" class="btn btn-warning btn-sm rounded-pill px-3">🌙</button>
      <a href="index.php" class="btn btn-light btn-sm rounded-pill px-3">🏠 Home</a>
    </div>
  </div>
</nav>

<div class="container mt-5">

  <h2 class="text-center mb-4 fw-bold">Your Shopping Cart</h2>

  <!-- ═══ CART TABLE ═════════════════════════════════════════════════════ -->
  <table class="table table-bordered text-center align-middle">
    <thead class="table-dark">
      <tr>
        <th>Product</th>
        <th>Price</th>
        <th>Quantity</th>
        <th>Total</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
    <?php while($row = mysqli_fetch_assoc($result)){
        $item_total = $row['price'] * $row['quantity'];
        $total += $item_total;
    ?>
      <tr>
        <td>
          <?php echo htmlspecialchars($row['name']); ?>
          <?php if(!empty($row['selected_color'] ?? '')): ?>
            <br><small class="text-muted">🎨 <?php echo htmlspecialchars($row['selected_color']); ?></small>
          <?php endif; ?>
        </td>
        <td>₹<?php echo $row['price']; ?></td>
        <td>
          <div class="d-flex align-items-center justify-content-center gap-2">
            <a href="update_quantity.php?id=<?php echo $row['product_id']; ?>&type=minus"
               class="qty-btn minus">−</a>
            <span class="qty-num"><?php echo $row['quantity']; ?></span>
            <a href="update_quantity.php?id=<?php echo $row['product_id']; ?>&type=plus"
               class="qty-btn plus">+</a>
          </div>
        </td>
        <td>₹<?php echo $item_total; ?></td>
        <td>
          <a href="remove.php?id=<?php echo $row['product_id']; ?>"
             class="btn btn-danger btn-sm rounded-pill px-3">Remove</a>
        </td>
      </tr>
    <?php } ?>
    </tbody>
  </table>


  <!-- ═══ ACCESSORIES SECTION ══════════════════════════════════════════ -->
  <?php $acc_query = mysqli_query($conn,"SELECT * FROM accessories"); ?>

  <div class="mt-4">
    <h5 class="fw-bold mb-3">🛠️ Add Accessories</h5>

    <div class="row g-3">
    <?php while($acc = mysqli_fetch_assoc($acc_query)){
        $acc_id  = $acc['id'];
        $qty_in_session = isset($_SESSION['accessories'][$acc_id]) ? $_SESSION['accessories'][$acc_id] : 0;
        $is_selected = $qty_in_session > 0;
    ?>
      <div class="col-6 col-md-2">
        <div class="acc-card <?php echo $is_selected ? 'selected' : ''; ?>">

          <img src="images/<?php echo htmlspecialchars($acc['image']); ?>"
               class="acc-img" alt="<?php echo htmlspecialchars($acc['name']); ?>">

          <div class="acc-name"><?php echo htmlspecialchars($acc['name']); ?></div>
          <div class="acc-price">₹<?php echo $acc['price']; ?></div>

          <!-- + / - buttons -->
          <div class="qty-controls">
            <a href="cart.php?acc_minus=<?php echo $acc_id; ?>"
               class="qty-btn minus">−</a>

            <span class="qty-num"><?php echo $qty_in_session; ?></span>

            <a href="cart.php?acc_plus=<?php echo $acc_id; ?>"
               class="qty-btn plus">+</a>
          </div>

        </div>
      </div>
    <?php } ?>
    </div>
  </div>

  <hr class="my-4">

  <!-- ═══ SUMMARY ══════════════════════════════════════════════════════ -->
  <div class="card card-summary border-0 shadow-sm p-4 ms-auto" style="max-width:420px; border-radius:14px; background:#fff;">

    <!-- Selected Accessories List -->
    <?php
    if(!empty($_SESSION['accessories'])){
        echo "<h6 class='fw-bold mb-3'>🧰 Selected Accessories</h6>";
        foreach($_SESSION['accessories'] as $acc_id => $qty){
            if($qty <= 0) continue;

            $q    = mysqli_query($conn,"SELECT name, price FROM accessories WHERE id=".intval($acc_id));
            $data = mysqli_fetch_assoc($q);

            // DB mein record nahi mila — purana session clean karo
            if(!$data){
                unset($_SESSION['accessories'][$acc_id]);
                continue;
            }

            $line = $data['price'] * $qty;
            $acc_total += $line;
            echo "
            <div class='acc-list-row'>
              <span>".htmlspecialchars($data['name'])." <span class='text-muted'>x{$qty}</span></span>
              <span class='fw-500'>₹{$line}</span>
            </div>";
        }
        echo "<hr>";
    }
    ?>

    <?php
    $grand_total  = $total + $acc_total;
    $subtotal     = round($grand_total / (1 + $GST_RATE/100), 2); // GST-exclusive base
    $gst_amount   = round($grand_total - $subtotal, 2);           // GST amount
    $cgst         = round($gst_amount / 2, 2);                    // CGST = 9%
    $sgst         = round($gst_amount / 2, 2);                    // SGST = 9%
    ?>

    <!-- Subtotal -->
    <div class="d-flex justify-content-between mb-1" style="font-size:14px;">
      <span class="text-muted">Subtotal (excl. GST)</span>
      <span>₹<?php echo number_format($subtotal, 2); ?></span>
    </div>

    <!-- GST Breakup -->
    <div class="d-flex justify-content-between mb-1" style="font-size:13px; color:#888;">
      <span>CGST (9%)</span>
      <span>₹<?php echo number_format($cgst, 2); ?></span>
    </div>
    <div class="d-flex justify-content-between mb-2" style="font-size:13px; color:#888;">
      <span>SGST (9%)</span>
      <span>₹<?php echo number_format($sgst, 2); ?></span>
    </div>
    <div class="d-flex justify-content-between mb-3" style="font-size:13px; font-weight:600; color:#e74c3c;">
      <span>Total GST (18%)</span>
      <span>₹<?php echo number_format($gst_amount, 2); ?></span>
    </div>

    <hr class="my-2">

    <div class="d-flex justify-content-between align-items-center mb-3">
      <span class="fw-bold fs-5">Total Cart Price</span>
      <span class="fw-bold fs-5 text-success">₹<?php echo number_format($grand_total, 2); ?></span>
    </div>

    <a href="checkout.php" class="btn btn-success w-100 rounded-pill py-2 fw-500">
      🛒 Proceed to Buy
    </a>

  </div>

</div><!-- /container -->


<!-- ═══ FOOTER ════════════════════════════════════════════════════════ -->
<div class="bg-dark text-light mt-5 py-4 text-center">
  <p class="mb-0">© 2026 My Shop | Designed by Karan tormal 💻</p>
</div>


<!-- ═══ DARK MODE JS ═════════════════════════════════════════════════ -->
<script>
window.onload = function() {
    if(localStorage.getItem("darkMode") === "enabled"){
        document.getElementById("body").classList.add("dark-mode");
    }
}
function toggleDarkMode() {
    let body = document.getElementById("body");
    body.classList.toggle("dark-mode");
    localStorage.setItem("darkMode", body.classList.contains("dark-mode") ? "enabled" : "disabled");
}
</script>

</body>
</html>