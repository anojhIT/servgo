<?php
session_start();
include("../config/db.php");
if(!isset($_SESSION['id']) || $_SESSION['role'] != 'user') {
    header("Location: ../login.php"); exit();
}

if(!isset($_GET['booking_id'])) {
    header("Location: my_bookings.php"); exit();
}

$booking_id = $_GET['booking_id'];
$user_id    = $_SESSION['id'];

$sql = "SELECT bookings.*, services.service_name, services.price,
               users.name AS customer_name, users.email AS customer_email,
               providers.name AS provider_name,
               categories.category_name
        FROM bookings
        JOIN services   ON bookings.service_id    = services.id
        JOIN users      ON bookings.user_id        = users.id
        JOIN users AS providers ON services.provider_id = providers.id
        JOIN categories ON services.category_id   = categories.id
        WHERE bookings.id = '$booking_id'
        AND bookings.user_id = '$user_id'";

$result  = mysqli_query($conn, $sql);
$booking = mysqli_fetch_assoc($result);

if(!$booking) {
    header("Location: my_bookings.php"); exit();
}

$transaction_id = 'TXN' . strtoupper(substr(md5($booking_id . $user_id), 0, 10));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Receipt #<?php echo str_pad($booking_id,5,'0',STR_PAD_LEFT); ?> — SERVGO</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Inter', system-ui, sans-serif;
      background: #F7F8FA; color: #111827;
      -webkit-font-smoothing: antialiased;
      padding: 2rem;
    }

    /* Action buttons - hidden when printing */
    .no-print {
      max-width: 680px; margin: 0 auto 1.5rem;
      display: flex; gap: 0.75rem;
    }
    .btn {
      display: inline-flex; align-items: center; gap: 0.4rem;
      padding: 0.65rem 1.25rem; border-radius: 8px; font-family: inherit;
      font-size: 0.875rem; font-weight: 600; cursor: pointer;
      border: none; text-decoration: none; transition: all 0.2s;
    }
    .btn-primary { background: #00C9A7; color: #0A0A14; }
    .btn-primary:hover { background: #00A888; }
    .btn-outline { background: transparent; color: #111827; border: 1.5px solid #E5E7EB; }
    .btn-outline:hover { border-color: #00C9A7; color: #00A888; }

    /* Receipt */
    .receipt {
      max-width: 680px; margin: 0 auto;
      background: #fff; border-radius: 16px;
      box-shadow: 0 4px 24px rgba(0,0,0,0.08);
      overflow: hidden;
    }

    /* Header */
    .receipt-header {
      background: linear-gradient(135deg, #0A0A14 0%, #1C1C2E 60%, #00A888 100%);
      padding: 2.5rem 2.5rem 2rem; color: #fff; position: relative; overflow: hidden;
    }
    .receipt-header::before {
      content: ''; position: absolute;
      top: -40px; right: -40px;
      width: 200px; height: 200px; border-radius: 50%;
      background: rgba(255,255,255,0.04);
    }
    .receipt-logo { font-size: 1.4rem; font-weight: 900; letter-spacing: -0.05em; margin-bottom: 1.5rem; }
    .receipt-logo span { color: #00C9A7; }
    .receipt-status {
      display: inline-flex; align-items: center; gap: 0.5rem;
      background: rgba(0,201,167,0.15); border: 1px solid rgba(0,201,167,0.3);
      color: #00C9A7; font-size: 0.78rem; font-weight: 700;
      letter-spacing: 0.06em; text-transform: uppercase;
      padding: 0.3rem 0.85rem; border-radius: 999px; margin-bottom: 1.25rem;
    }
    .receipt-title { font-size: 1.75rem; font-weight: 900; letter-spacing: -0.04em; margin-bottom: 0.25rem; }
    .receipt-sub   { color: rgba(255,255,255,0.45); font-size: 0.875rem; }

    /* Body */
    .receipt-body { padding: 2rem 2.5rem; }

    .receipt-section { margin-bottom: 1.75rem; }
    .receipt-section-title {
      font-size: 0.68rem; font-weight: 700; text-transform: uppercase;
      letter-spacing: 0.1em; color: #9CA3AF; margin-bottom: 0.85rem;
      padding-bottom: 0.5rem; border-bottom: 1px solid #F3F4F6;
    }

    .receipt-row {
      display: flex; justify-content: space-between; align-items: center;
      padding: 0.55rem 0; font-size: 0.875rem;
    }
    .receipt-row-label { color: #6B7280; }
    .receipt-row-value { font-weight: 600; color: #111827; text-align: right; }

    /* Divider dashes */
    .receipt-dash {
      border: none; border-top: 2px dashed #E5E7EB; margin: 1.25rem 0;
    }

    /* Total box */
    .receipt-total {
      background: #F7F8FA; border-radius: 12px; padding: 1.25rem 1.5rem;
      display: flex; justify-content: space-between; align-items: center;
      margin-bottom: 1.75rem;
    }
    .receipt-total-label { font-size: 1rem; font-weight: 700; }
    .receipt-total-value { font-size: 1.75rem; font-weight: 900; letter-spacing: -0.04em; color: #00A888; }

    /* Status badge */
    .status-badge {
      display: inline-flex; align-items: center; gap: 0.4rem;
      padding: 0.3rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700;
    }
    .status-pending   { background: #FEF3C7; color: #92400E; }
    .status-accepted  { background: #D1FAE5; color: #065F46; }
    .status-completed { background: #DBEAFE; color: #1E40AF; }
    .status-declined  { background: #FEE2E2; color: #991B1B; }

    /* Barcode decoration */
    .receipt-barcode {
      display: flex; gap: 3px; justify-content: center;
      margin: 1.5rem 0 0.5rem; opacity: 0.15;
    }
    .receipt-barcode span {
      display: inline-block; width: 2px; background: #111827;
    }

    /* Transaction ID */
    .receipt-txn {
      text-align: center; font-size: 0.72rem; color: #9CA3AF;
      letter-spacing: 0.12em; font-weight: 600; text-transform: uppercase;
      margin-bottom: 1.5rem;
    }

    /* Footer */
    .receipt-footer {
      background: #F7F8FA; padding: 1.25rem 2.5rem;
      text-align: center; font-size: 0.78rem; color: #9CA3AF; line-height: 1.7;
      border-top: 1px solid #E5E7EB;
    }

    /* Print styles */
    @media print {
      body { background: #fff; padding: 0; }
      .no-print { display: none !important; }
      .receipt { box-shadow: none; border-radius: 0; max-width: 100%; }
    }
  </style>
</head>
<body>

<!-- Action buttons (hidden on print) -->
<div class="no-print">
  <button onclick="window.print()" class="btn btn-primary">🖨️ Print Receipt</button>
  <a href="my_bookings.php" class="btn btn-outline">← Back to Bookings</a>
</div>

<!-- Receipt -->
<div class="receipt">

  <!-- Header -->
  <div class="receipt-header">
    <div class="receipt-logo">SERV<span>GO</span></div>
    <div class="receipt-status">✓ Booking Confirmed</div>
    <div class="receipt-title">Payment Receipt</div>
    <div class="receipt-sub">Thank you for using SERVGO — your booking is confirmed.</div>
  </div>

  <!-- Body -->
  <div class="receipt-body">

    <!-- Transaction Info -->
    <div class="receipt-section">
      <div class="receipt-section-title">Transaction Details</div>
      <div class="receipt-row">
        <span class="receipt-row-label">Booking ID</span>
        <span class="receipt-row-value">#<?php echo str_pad($booking_id,5,'0',STR_PAD_LEFT); ?></span>
      </div>
      <div class="receipt-row">
        <span class="receipt-row-label">Transaction ID</span>
        <span class="receipt-row-value" style="color:#00A888;"><?php echo $transaction_id; ?></span>
      </div>
      <div class="receipt-row">
        <span class="receipt-row-label">Date & Time</span>
        <span class="receipt-row-value"><?php echo date('d M Y, h:i A', strtotime($booking['booking_date'])); ?></span>
      </div>
      <div class="receipt-row">
        <span class="receipt-row-label">Status</span>
        <span class="receipt-row-value">
          <?php
            $s = strtolower($booking['status']);
            $map = ['pending'=>'status-pending','accepted'=>'status-accepted','completed'=>'status-completed','declined'=>'status-declined'];
            $cls = $map[$s] ?? 'status-pending';
          ?>
          <span class="status-badge <?php echo $cls; ?>"><?php echo $booking['status']; ?></span>
        </span>
      </div>
    </div>

    <hr class="receipt-dash">

    <!-- Service Info -->
    <div class="receipt-section">
      <div class="receipt-section-title">Service Details</div>
      <div class="receipt-row">
        <span class="receipt-row-label">Service Name</span>
        <span class="receipt-row-value"><?php echo htmlspecialchars($booking['service_name']); ?></span>
      </div>
      <div class="receipt-row">
        <span class="receipt-row-label">Category</span>
        <span class="receipt-row-value"><?php echo htmlspecialchars($booking['category_name']); ?></span>
      </div>
      <div class="receipt-row">
        <span class="receipt-row-label">Service Provider</span>
        <span class="receipt-row-value"><?php echo htmlspecialchars($booking['provider_name']); ?></span>
      </div>
    </div>

    <hr class="receipt-dash">

    <!-- Customer Info -->
    <div class="receipt-section">
      <div class="receipt-section-title">Customer Details</div>
      <div class="receipt-row">
        <span class="receipt-row-label">Name</span>
        <span class="receipt-row-value"><?php echo htmlspecialchars($booking['customer_name']); ?></span>
      </div>
      <div class="receipt-row">
        <span class="receipt-row-label">Email</span>
        <span class="receipt-row-value"><?php echo htmlspecialchars($booking['customer_email']); ?></span>
      </div>
    </div>

    <hr class="receipt-dash">

    <!-- Payment Breakdown -->
    <div class="receipt-section">
      <div class="receipt-section-title">Payment Breakdown</div>
      <div class="receipt-row">
        <span class="receipt-row-label">Service Price</span>
        <span class="receipt-row-value">Rs. <?php echo number_format($booking['price'],2); ?></span>
      </div>
      <div class="receipt-row">
        <span class="receipt-row-label">Service Fee</span>
        <span class="receipt-row-value" style="color:#00A888;">Free</span>
      </div>
      <div class="receipt-row">
        <span class="receipt-row-label">Tax</span>
        <span class="receipt-row-value">Rs. 0.00</span>
      </div>
    </div>

    <!-- Total -->
    <div class="receipt-total">
      <span class="receipt-total-label">Total Paid</span>
      <span class="receipt-total-value">Rs. <?php echo number_format($booking['price'],2); ?></span>
    </div>

    <!-- Barcode decoration -->
    <div class="receipt-barcode">
      <?php
        $heights = [35,20,30,15,40,25,35,20,45,30,20,35,25,40,15,30,20,35,25,40,30,20,35,45,25,30,20,35,15,40];
        foreach($heights as $h) {
            echo '<span style="height:'.$h.'px;"></span>';
        }
      ?>
    </div>
    <div class="receipt-txn"><?php echo $transaction_id; ?></div>

  </div>

  <!-- Footer -->
  <div class="receipt-footer">
    <strong>SERVGO</strong> · Omanthai, Vavuniya, Sri Lanka<br>
    📞 0755988074 · ✉️ info@servgo.com<br>
    This is an official booking receipt. Keep this for your records.
  </div>

</div>

</body>
</html>
