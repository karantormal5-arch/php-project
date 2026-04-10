<?php
session_start();
include("config.php");

$user_id = $_SESSION['user_id'];

/* ── User Name ────────────────────────────────────────────────────────── */
$user_res  = mysqli_query($conn, "SELECT name FROM users WHERE id='$user_id'");
$user_data = mysqli_fetch_assoc($user_res);
$user_name = $user_data['name'] ?? 'Customer';

/* ── Address ──────────────────────────────────────────────────────────── */
$result  = mysqli_query($conn,"
    SELECT * FROM user_addresses
    WHERE user_id='$user_id'
    ORDER BY id DESC LIMIT 1
");
$address = mysqli_fetch_assoc($result);

/* ── Cart ─────────────────────────────────────────────────────────────── */
$cart = mysqli_query($conn,"
    SELECT products.name, products.price, cart.quantity
    FROM cart
    JOIN products ON cart.product_id = products.id
    WHERE cart.user_id='$user_id'
");

/* ── Cart data array mein store karo — 2 baar use hoga (summary + modal) */
$cart_items = [];
while($row = mysqli_fetch_assoc($cart)){
    $cart_items[] = $row;
}

/* ── Accessories bhi array mein store karo ── */
$acc_items = [];
$acc_total = 0;
if(!empty($_SESSION['accessories'])){
    foreach($_SESSION['accessories'] as $acc_id => $qty){
        if($qty <= 0) continue;
        $acc_q    = mysqli_query($conn, "SELECT name, price FROM accessories WHERE id=".intval($acc_id));
        $acc_data = mysqli_fetch_assoc($acc_q);
        if(!$acc_data) continue;
        $line       = $acc_data['price'] * $qty;
        $acc_total += $line;
        $acc_items[] = [
            'name'  => $acc_data['name'],
            'price' => $acc_data['price'],
            'qty'   => $qty,
            'line'  => $line
        ];
    }
}

$total = 0;
foreach($cart_items as $item){
    $total += $item['price'] * $item['quantity'];
}

$GST_RATE   = 18;
$grand_total = $total + $acc_total;
$subtotal    = round($grand_total / (1 + $GST_RATE/100), 2);
$gst_amount  = round($grand_total - $subtotal, 2);
$cgst        = round($gst_amount / 2, 2);
$sgst        = round($gst_amount / 2, 2);
?>

<!DOCTYPE html>
<html>
<head>
<title>Checkout</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
body { font-family: 'Poppins', sans-serif; background: #f1f3f6; }

/* ── Bill Modal ── */
.bill-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.55);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.bill-modal-overlay.show { display: flex; }
.bill-modal {
    background: #fff;
    border-radius: 8px;
    width: 100%;
    max-width: 560px;
    max-height: 90vh;
    overflow-y: auto;
    border: 2px solid #333;
    animation: slideUp 0.25s ease;
}
@keyframes slideUp {
    from { transform: translateY(30px); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}

/* ── Bill Header ── */
.bill-header {
    background: #fff;
    border-bottom: 2px solid #333;
    padding: 16px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.bill-header .shop-name { font-size: 18px; font-weight: 700; color: #000; }
.bill-header .bill-label { font-size: 12px; color: #666; margin-top: 2px; }

/* ── Bill Body ── */
.bill-body { padding: 16px 24px; }
.bill-section-title {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #333;
    margin: 14px 0 6px;
    border-bottom: 1px solid #ccc;
    padding-bottom: 4px;
}
.bill-row {
    display: flex;
    justify-content: space-between;
    font-size: 14px;
    padding: 5px 0;
    border-bottom: 1px solid #eee;
}
.bill-row:last-child { border-bottom: none; }
.bill-row .name { color: #333; }
.bill-row .amt  { font-weight: 500; color: #000; }
.bill-gst-row {
    display: flex;
    justify-content: space-between;
    font-size: 13px;
    padding: 4px 0;
    color: #555;
}
.bill-gst-total {
    display: flex;
    justify-content: space-between;
    font-size: 13px;
    font-weight: 700;
    color: #000;
    padding: 4px 0;
    border-top: 1px solid #ccc;
    margin-top: 4px;
}
.bill-grand {
    display: flex;
    justify-content: space-between;
    font-size: 18px;
    font-weight: 700;
    padding: 12px 0 0;
    border-top: 2px solid #333;
    margin-top: 10px;
    color: #000;
}
.bill-grand .grand-amt { color: #000; }

/* ── Modal Buttons ── */
.bill-footer {
    padding: 16px 24px 20px;
    display: flex;
    gap: 10px;
    border-top: 1px solid #ddd;
}
.bill-footer button, .bill-footer a {
    flex: 1;
    padding: 10px;
    border-radius: 6px;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    font-weight: 500;
    border: 1px solid #333;
    cursor: pointer;
    text-align: center;
    text-decoration: none;
    background: #fff;
    color: #333;
    transition: background 0.15s;
}
.bill-footer button:hover, .bill-footer a:hover { background: #f0f0f0; }
.btn-confirm { background: #333 !important; color: #fff !important; border-color: #333 !important; }
.btn-confirm:hover { background: #555 !important; }
</style>
</head>

<body style="background:#f1f3f6;">

<!-- Navbar -->
<nav class="navbar navbar-dark bg-dark px-4 mb-3">
  <span class="navbar-brand">🏍️ My Shop</span>
  <span class="text-white">👤 <?php echo htmlspecialchars($user_name); ?></span>
  <a href="index.php" class="btn btn-light btn-sm">🏠 Home</a>
</nav>

<div class="container mt-3">
<div class="row">

  <!-- Address + Payment -->
  <div class="col-md-7">

    <div class="card p-3 mb-4">
      <h5 class="text-primary">📍 Delivery Address</h5>
      <?php if($address){ ?>
        <p><b>Phone:</b>   <?php echo htmlspecialchars($address['phone']);   ?></p>
        <p><b>Village:</b> <?php echo htmlspecialchars($address['village']); ?></p>
        <p><b>Area:</b>    <?php echo htmlspecialchars($address['area']);    ?></p>
        <p><b>City:</b>    <?php echo htmlspecialchars($address['city']);    ?></p>
        <p><b>State:</b>   <?php echo htmlspecialchars($address['state']);   ?></p>
        <p><b>Pincode:</b> <?php echo htmlspecialchars($address['pincode']); ?></p>
        <p><b>Address:</b><br><?php echo htmlspecialchars($address['address']); ?></p>
        <a href="add_address.php" class="btn btn-primary btn-sm">Change Address</a>
      <?php } else { ?>
        <p>No Address Added</p>
        <a href="add_address.php" class="btn btn-success btn-sm">Add Address</a>
      <?php } ?>
    </div>

    <div class="card p-3">
      <h5>Payment Options</h5>

      <!-- FIX: form action place_order.php — modal se submit hoga -->
      <form action="place_order.php" method="POST" id="orderForm">
        <input type="radio" name="payment" value="COD" required id="cod"> Cash On Delivery
        <br><br>
        <input type="radio" name="payment" value="UPI" id="upi"> UPI Payment
        <br><br>
        <!-- Place Order → pehle modal dikhao -->
        <button type="button" class="btn btn-success" onclick="showBillModal()">
          Place Order
        </button>
      </form>
    </div>

  </div>

  <!-- Order Summary -->
  <div class="col-md-5">
    <div class="card p-3">
      <h5>Order Summary</h5>
      <p class="text-muted" style="font-size:13px;">
        <b><?php echo htmlspecialchars($user_name); ?></b>
      </p>
      <hr>

      <!-- Cart Products -->
      <?php foreach($cart_items as $item):
          $item_total = $item['price'] * $item['quantity'];
      ?>
        <p>
          <?php echo htmlspecialchars($item['name']); ?>
          <br>
          Qty: <?php echo $item['quantity']; ?>
          <span class="float-end">₹<?php echo number_format($item_total); ?></span>
        </p>
        <hr>
      <?php endforeach; ?>

      <!-- Accessories -->
      <?php if(!empty($acc_items)): ?>
        <h6 class="text-primary">Accessories</h6>
        <hr>
        <?php foreach($acc_items as $acc): ?>
          <p>
            <b><?php echo htmlspecialchars($acc['name']); ?></b>
            <small class="text-muted"> x<?php echo $acc['qty']; ?></small>
            <span class="float-end">₹<?php echo number_format($acc['line']); ?></span>
          </p>
          <hr>
        <?php endforeach; ?>
        <h6>Accessories Total
          <span class="float-end">₹<?php echo number_format($acc_total); ?></span>
        </h6>
        <hr>
      <?php endif; ?>

      <!-- GST Breakup -->
      <div class="d-flex justify-content-between mb-1" style="font-size:14px;">
        <span class="text-muted">Subtotal (excl. GST)</span>
        <span>₹<?php echo number_format($subtotal, 2); ?></span>
      </div>
      <div class="d-flex justify-content-between mb-1" style="font-size:13px; color:#888;">
        <span>CGST (9%)</span>
        <span>₹<?php echo number_format($cgst, 2); ?></span>
      </div>
      <div class="d-flex justify-content-between mb-2" style="font-size:13px; color:#888;">
        <span>SGST (9%)</span>
        <span>₹<?php echo number_format($sgst, 2); ?></span>
      </div>
      <div class="d-flex justify-content-between mb-2" style="font-size:13px; font-weight:600; color:#e74c3c;">
        <span>Total GST (18%)</span>
        <span>₹<?php echo number_format($gst_amount, 2); ?></span>
      </div>
      <hr>
      <h5>Total Amount
        <span class="float-end text-success">₹<?php echo number_format($grand_total, 2); ?></span>
      </h5>
    </div>

    <a href="cart.php" class="btn btn-dark mt-3 w-100 rounded-pill">🛒 Back to Cart</a>

  </div>

</div>
</div>


<!-- ══ BILL PREVIEW MODAL ═══════════════════════════════════════════════ -->
<div class="bill-modal-overlay" id="billModal" onclick="closeBillModal(event)">
  <div class="bill-modal">

    <!-- Header -->
    <div class="bill-header">
      <div>
        <div class="shop-name">My Shop — Bill Preview</div>
        <div class="bill-label">Please review before confirming</div>
      </div>
      <div style="font-size:13px; color:#666;">
        <?php echo date('d M Y'); ?>
      </div>
    </div>

    <div class="bill-body">

      <!-- Customer -->
      <div class="bill-section-title">Customer</div>
      <div class="bill-row">
        <span class="name"><?php echo htmlspecialchars($user_name); ?></span>
        <span class="amt" id="modalPayment">—</span>
      </div>
      <?php if($address): ?>
      <div class="bill-row">
        <span class="name" style="color:#888; font-size:13px;">
          <?php echo htmlspecialchars($address['city']); ?>,
          <?php echo htmlspecialchars($address['state']); ?> —
          <?php echo htmlspecialchars($address['pincode']); ?>
        </span>
      </div>
      <?php endif; ?>

      <!-- Products -->
      <div class="bill-section-title">Products</div>
      <?php foreach($cart_items as $item):
          $item_total = $item['price'] * $item['quantity'];
      ?>
      <div class="bill-row">
        <span class="name"><?php echo htmlspecialchars($item['name']); ?> <span style="color:#aaa;">x<?php echo $item['quantity']; ?></span></span>
        <span class="amt">₹<?php echo number_format($item_total); ?></span>
      </div>
      <?php endforeach; ?>

      <!-- Accessories -->
      <?php if(!empty($acc_items)): ?>
      <div class="bill-section-title">🛠️ Accessories</div>
      <?php foreach($acc_items as $acc): ?>
      <div class="bill-row">
        <span class="name"><?php echo htmlspecialchars($acc['name']); ?> <span style="color:#aaa;">x<?php echo $acc['qty']; ?></span></span>
        <span class="amt">₹<?php echo number_format($acc['line']); ?></span>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>

      <!-- GST -->
      <div class="bill-section-title">Tax Breakup</div>
      <div class="bill-gst-row">
        <span>Subtotal (excl. GST)</span>
        <span>₹<?php echo number_format($subtotal, 2); ?></span>
      </div>
      <div class="bill-gst-row">
        <span>CGST (9%)</span>
        <span>₹<?php echo number_format($cgst, 2); ?></span>
      </div>
      <div class="bill-gst-row">
        <span>SGST (9%)</span>
        <span>₹<?php echo number_format($sgst, 2); ?></span>
      </div>
      <div class="bill-gst-total">
        <span>Total GST (18%)</span>
        <span>₹<?php echo number_format($gst_amount, 2); ?></span>
      </div>

      <!-- Grand Total -->
      <div class="bill-grand">
        <span>Grand Total</span>
        <span class="grand-amt">₹<?php echo number_format($grand_total, 2); ?></span>
      </div>

    </div><!-- /bill-body -->

    <!-- Action Buttons -->
    <div class="bill-footer">
      <a href="cart.php" class="btn-cart">🛒 Back to Cart</a>
      <button class="btn-confirm" onclick="confirmOrder()">✅Confirm Order</button>
    </div>

  </div>
</div>


<!-- ══ JS ═══════════════════════════════════════════════════════════════ -->
<script>
function showBillModal() {
    // Payment method check
    const cod = document.getElementById('cod');
    const upi = document.getElementById('upi');
    if(!cod.checked && !upi.checked){
        alert('Please select a payment method first!');
        return;
    }
    // Modal mein payment method dikhao
    const payLabel = cod.checked ? 'Cash on Delivery' : 'UPI Payment';
    document.getElementById('modalPayment').textContent = payLabel;

    // Modal open
    document.getElementById('billModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeBillModal(event) {
    // Sirf overlay click pe close ho — modal ke andar click pe nahi
    if(event.target === document.getElementById('billModal')){
        document.getElementById('billModal').classList.remove('show');
        document.body.style.overflow = '';
    }
}

function confirmOrder() {
    // Form submit karo
    document.getElementById('orderForm').submit();
}
</script>

</body>
</html>

