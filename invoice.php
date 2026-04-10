<?php
session_start();
include("config.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$order_id = intval($_GET['order_id']);
$user_id  = $_SESSION['user_id'];

/* ── Order + User ── */
$order = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT orders.*, users.name AS user_name, users.email
     FROM orders
     JOIN users ON orders.user_id = users.id
     WHERE orders.id='$order_id' AND orders.user_id='$user_id'"
));

if(!$order){ echo "<h3 style='text-align:center;margin-top:60px;'>Order not found.</h3>"; exit(); }

/* ── Address ── */
$addr = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM user_addresses WHERE user_id='$user_id' ORDER BY id DESC LIMIT 1"
));

/* ── Products ── */
$items_res  = mysqli_query($conn,
    "SELECT order_items.*, products.name
     FROM order_items
     JOIN products ON order_items.product_id = products.id
     WHERE order_items.order_id='$order_id'"
);
$items_arr  = [];
$prod_total = 0;
while($row = mysqli_fetch_assoc($items_res)){
    $row['line'] = $row['price'] * $row['quantity'];
    $prod_total += $row['line'];
    $items_arr[] = $row;
}

/* ── Accessories ── */
$acc_res   = mysqli_query($conn, "SELECT * FROM order_accessories WHERE order_id='$order_id'");
$acc_rows  = [];
$acc_total = 0;
while($a = mysqli_fetch_assoc($acc_res)){
    $a['line'] = $a['price'] * $a['quantity'];
    $acc_total += $a['line'];
    $acc_rows[] = $a;
}

/* ── GST ── */
$grand_total = $prod_total + $acc_total;
$subtotal    = round($grand_total / 1.18, 2);
$gst_amount  = round($grand_total - $subtotal, 2);
$cgst        = round($gst_amount / 2, 2);
$sgst        = round($gst_amount / 2, 2);
?>
<!DOCTYPE html>
<html>
<head>
<title>Invoice #<?php echo str_pad($order_id,6,'0',STR_PAD_LEFT); ?></title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*{ box-sizing:border-box; margin:0; padding:0; }
body{ background:#e8eaf0; font-family:'Poppins',sans-serif; padding:30px 16px 60px; }

.action-bar{
    max-width:800px; margin:0 auto 16px;
    display:flex; gap:10px; justify-content:flex-end;
}
.action-bar a, .action-bar button{
    padding:9px 22px; border-radius:30px;
    font-family:'Poppins',sans-serif; font-size:13px; font-weight:500;
    border:none; cursor:pointer; text-decoration:none; transition:opacity 0.15s;
}
.action-bar a:hover,.action-bar button:hover{ opacity:0.85; }
.btn-print{ background:#1a1a2e; color:#fff; }
.btn-home { background:#198754; color:#fff; }
.btn-cart { background:#0d6efd; color:#fff; }

.invoice{
    max-width:800px; margin:0 auto;
    background:#fff; border-radius:14px;
    overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,0.12);
}

/* Header */
.inv-header{
    background:#1a1a2e; color:#fff;
    padding:28px 36px;
    display:flex; justify-content:space-between; align-items:flex-start;
}
.shop-name{ font-size:24px; font-weight:700; letter-spacing:1px; }
.shop-sub { font-size:12px; color:#aac4e8; margin-top:4px; }
.inv-right{ text-align:right; }
.inv-no   { font-size:20px; font-weight:700; color:#ffd700; letter-spacing:2px; }
.inv-date { font-size:12px; color:#ccc; margin-top:4px; }

.success-strip{
    background:#e6f9f0; border-left:5px solid #198754;
    padding:12px 36px; font-size:13px; font-weight:500; color:#145c38;
    display:flex; align-items:center; gap:10px;
}

.inv-body{ padding:28px 36px; }

.info-grid{
    display:grid; grid-template-columns:1fr 1fr;
    gap:16px; margin-bottom:26px;
}
.info-box{
    background:#f8f9fb; border-radius:10px;
    padding:14px 16px; border:1px solid #e8eaf0;
}
.info-title{
    font-size:11px; font-weight:600;
    text-transform:uppercase; letter-spacing:1px;
    color:#888; margin-bottom:8px;
}
.info-row{ display:flex; gap:8px; font-size:13px; padding:2px 0; }
.info-key{ color:#888; min-width:68px; }
.info-val{ color:#222; font-weight:500; }
.pay-badge{ display:inline-block; padding:3px 12px; border-radius:20px; font-size:11px; font-weight:600; }
.pay-cod{ background:#fff3cd; color:#856404; }
.pay-upi{ background:#d1ecf1; color:#0c5460; }

.section-heading{
    font-size:14px; font-weight:600;
    margin:20px 0 10px; color:#1a1a2e;
    padding-bottom:6px; border-bottom:2px solid #1a1a2e;
}

.inv-table{ width:100%; border-collapse:collapse; font-size:13px; margin-bottom:20px; }
.inv-table thead tr{ background:#1a1a2e; color:#fff; }
.inv-table thead th{ padding:11px 14px; font-weight:500; }
.inv-table tbody tr{ border-bottom:1px solid #f0f0f0; }
.inv-table tbody tr:last-child{ border-bottom:none; }
.inv-table tbody td{ padding:11px 14px; color:#333; vertical-align:middle; }
.tr{ text-align:right; }
.tc{ text-align:center; }
.acc-table thead tr{ background:#0d6efd; }

.totals-wrap{ display:flex; justify-content:flex-end; margin-bottom:20px; }
.totals-box{ min-width:300px; }
.tot-row{
    display:flex; justify-content:space-between;
    padding:6px 0; font-size:14px;
    border-bottom:1px solid #f0f0f0; color:#444;
}
.tot-row.gst-line{ font-size:13px; color:#888; }
.tot-row.gst-total{ font-size:13px; font-weight:600; color:#e74c3c; border-bottom:none; }
.tot-row.grand{
    font-size:18px; font-weight:700;
    padding-top:12px; border-top:2px solid #1a1a2e;
    border-bottom:none; color:#1a1a2e;
}
.tot-row.grand .g-amt{ color:#198754; }

.inv-footer{
    background:#f8f9fb; border-top:1px solid #e8eaf0;
    padding:16px 36px;
    display:flex; justify-content:space-between;
    font-size:12px; color:#888;
}

@media print{
    body{ background:#fff; padding:0; }
    .action-bar{ display:none; }
    .success-strip{ display:none; }
    .invoice{ box-shadow:none; border-radius:0; }
}
</style>
</head>
<body>

<div class="action-bar">
    <a href="cart.php"  class="btn-cart">🛒 Back to Cart</a>
    <a href="index.php" class="btn-home">🏠 Continue Shopping</a>
    <button class="btn-print" onclick="window.print()">🖨️ Print Invoice</button>
</div>

<div class="invoice">

  <div class="inv-header">
    <div>
      <div class="shop-name">🏍️ My Bike Shop</div>
      <div class="shop-sub">chha.sambhajinager, Maharashtra &nbsp;|&nbsp; Phone: 9509068409</div>
    </div>
    <div class="inv-right">
      <div class="inv-no">INVOICE</div>
      <div class="inv-date"># <?php echo str_pad($order_id,6,'0',STR_PAD_LEFT); ?></div>
      <div class="inv-date"><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></div>
    </div>
  </div>

  <div class="success-strip">
    ✅ &nbsp; Order placed! Thank you, <strong>&nbsp;<?php echo htmlspecialchars($order['user_name']); ?></strong>.
  </div>

  <div class="inv-body">

    <div class="info-grid">
      <div class="info-box">
        <div class="info-title">👤 Customer Details</div>
        <div class="info-row"><span class="info-key">Name</span>    <span class="info-val"><?php echo htmlspecialchars($order['user_name']); ?></span></div>
        <?php if(!empty($order['email'])): ?>
        <div class="info-row"><span class="info-key">Email</span>   <span class="info-val"><?php echo htmlspecialchars($order['email']); ?></span></div>
        <?php endif; ?>
        <div class="info-row">
          <span class="info-key">Payment</span>
          <span class="info-val">
            <?php if($order['payment_method'] === 'UPI'): ?>
              <span class="pay-badge pay-upi">📱 UPI</span>
            <?php else: ?>
              <span class="pay-badge pay-cod">🚚 Cash on Delivery</span>
            <?php endif; ?>
          </span>
        </div>
        <div class="info-row"><span class="info-key">Order ID</span><span class="info-val"># <?php echo str_pad($order_id,6,'0',STR_PAD_LEFT); ?></span></div>
      </div>

      <div class="info-box">
        <div class="info-title">📍 Delivery Address</div>
        <?php if($addr): ?>
          <div class="info-row"><span class="info-key">Name</span>   <span class="info-val"><?php echo htmlspecialchars($order['user_name']); ?></span></div>
          <div class="info-row"><span class="info-key">Phone</span>  <span class="info-val"><?php echo htmlspecialchars($addr['phone']);   ?></span></div>
          <div class="info-row"><span class="info-key">Village</span><span class="info-val"><?php echo htmlspecialchars($addr['village']); ?></span></div>
          <div class="info-row"><span class="info-key">City</span>   <span class="info-val"><?php echo htmlspecialchars($addr['city']);    ?></span></div>
          <div class="info-row"><span class="info-key">State</span>  <span class="info-val"><?php echo htmlspecialchars($addr['state']);   ?></span></div>
          <div class="info-row"><span class="info-key">Pincode</span><span class="info-val"><?php echo htmlspecialchars($addr['pincode']); ?></span></div>
          <div class="info-row"><span class="info-key">Address</span><span class="info-val"><?php echo htmlspecialchars($addr['address']); ?></span></div>
        <?php else: ?>
          <p style="font-size:13px;color:#888;">No address on file.</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- ══ PRODUCTS — sirf tab dikhao jab products hain ══ -->
    <?php if(!empty($items_arr)): ?>
    <div class="section-heading">Product Details</div>
    <table class="inv-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Product</th>
          <th class="tc">Qty</th>
          <th class="tr">Unit Price</th>
          <th class="tr">Total</th>
        </tr>
      </thead>
      <tbody>
        <?php $sr=1; foreach($items_arr as $item): ?>
        <tr>
          <td><?php echo $sr++; ?></td>
          <td><?php echo htmlspecialchars($item['name']); ?></td>
          <td class="tc"><?php echo $item['quantity']; ?></td>
          <td class="tr">₹<?php echo number_format($item['price']); ?></td>
          <td class="tr">₹<?php echo number_format($item['line']); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>

    <!-- ══ ACCESSORIES — sirf tab dikhao jab accessories hain ══ -->
    <?php if(!empty($acc_rows)): ?>
    <div class="section-heading">Accessories</div>
    <table class="inv-table ">
      <thead>
        <tr>
          <th>#</th>
          <th>Item</th>
          <th class="tc">Qty</th>
          <th class="tr">Unit Price</th>
          <th class="tr">Total</th>
        </tr>
      </thead>
      <tbody>
        <?php $asr=1; foreach($acc_rows as $acc): ?>
        <tr>
          <td><?php echo $asr++; ?></td>
          <td><?php echo htmlspecialchars($acc['acc_name']); ?></td>
          <td class="tc"><?php echo $acc['quantity']; ?></td>
          <td class="tr">₹<?php echo number_format($acc['price']); ?></td>
          <td class="tr">₹<?php echo number_format($acc['line']); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>

    <!-- ══ TOTALS ══ -->
    <div class="totals-wrap">
      <div class="totals-box">
        <?php if(!empty($items_arr)):?>
        <div class="tot-row"><span>Products Total</span><span>₹<?php echo number_format($prod_total); ?></span></div>
        <?php endif; ?>
        <?php if($acc_total > 0): ?>
        <div class="tot-row"><span>Accessories Total</span><span>₹<?php echo number_format($acc_total); ?></span></div>
        <?php endif; ?>
        <div class="tot-row"><span>Subtotal (excl. GST)</span><span>₹<?php echo number_format($subtotal, 2); ?></span></div>
        <div class="tot-row gst-line"><span>CGST (9%)</span><span>₹<?php echo number_format($cgst, 2); ?></span></div>
        <div class="tot-row gst-line"><span>SGST (9%)</span><span>₹<?php echo number_format($sgst, 2); ?></span></div>
        <div class="tot-row gst-total"><span>Total GST (18%)</span><span>₹<?php echo number_format($gst_amount, 2); ?></span></div>
        <div class="tot-row"><span>Delivery</span><span style="color:#198754;font-weight:600;">FREE</span></div>
        <div class="tot-row grand"><span>Grand Total</span><span class="g-amt">₹<?php echo number_format($grand_total, 2); ?></span></div>
      </div>
    </div>

  </div>

  <div class="inv-footer">
    <span>🙏 Thank you for shopping with My Bike Shop!</span>
    <span>© 2026 My Shop — Designed by Karan tormal</span>
  </div>

</div>
</body>
</html>