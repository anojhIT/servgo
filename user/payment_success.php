<?php
session_start();
include("../config/db.php");

if(!isset($_SESSION['id']) || $_SESSION['role'] != 'user') {
    header("Location: ../login.php"); exit();
}

if(!isset($_POST['service_id'])) {
    header("Location: services.php"); exit();
}

$user_id        = $_SESSION['id'];
$service_id     = $_POST['service_id'];
$amount         = $_POST['amount'];
$payment_method = $_POST['payment_method'] ?? 'Card';

/* Generate fake transaction ID */
$transaction_id = 'TXN' . strtoupper(substr(md5(uniqid()), 0, 10));

/* Save booking to database */
$sql = "INSERT INTO bookings (user_id, service_id, booking_date, status)
        VALUES ('$user_id', '$service_id', NOW(), 'Pending')";
mysqli_query($conn, $sql);
$booking_id = mysqli_insert_id($conn);

/* Get service details for display */
$svc_res = mysqli_query($conn,
    "SELECT services.*, users.name AS provider_name, categories.category_name
     FROM services
     JOIN users      ON services.provider_id = users.id
     JOIN categories ON services.category_id = categories.id
     WHERE services.id = '$service_id'"
);
$service = mysqli_fetch_assoc($svc_res);

include("../includes/header.php");
?>

<div style="min-height:100vh;background:var(--off-white);padding-top:80px;display:flex;align-items:center;justify-content:center;padding:100px 2rem 4rem;">
<div style="max-width:560px;width:100%;margin:0 auto;">

  <!-- Success animation card -->
  <div class="form-card" style="text-align:center;padding:3rem 2.5rem;">

    <!-- Animated checkmark -->
    <div id="success-circle" style="
      width:90px;height:90px;border-radius:50%;
      background:linear-gradient(135deg,var(--teal),var(--teal-d));
      display:flex;align-items:center;justify-content:center;
      margin:0 auto 1.75rem;
      box-shadow:0 12px 36px rgba(0,201,167,0.35);
      font-size:2.5rem;
      animation: popIn 0.5s cubic-bezier(.22,1,.36,1) both;
    ">✓</div>

    <h1 style="font-size:1.75rem;font-weight:900;letter-spacing:-0.04em;color:var(--text);margin-bottom:0.5rem;">
      Payment Successful!
    </h1>
    <p style="color:var(--muted);font-size:0.95rem;margin-bottom:2rem;line-height:1.7;">
      Your booking has been confirmed and is waiting for admin approval.
      You will be notified once it is accepted.
    </p>

    <!-- Transaction details box -->
    <div style="background:var(--off-white);border-radius:12px;padding:1.5rem;text-align:left;margin-bottom:2rem;">

      <div style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.09em;color:var(--muted);margin-bottom:1rem;">
        Booking Details
      </div>

      <div style="display:flex;flex-direction:column;gap:0.75rem;">

        <div style="display:flex;justify-content:space-between;align-items:center;">
          <span style="font-size:0.82rem;color:var(--muted);">Booking ID</span>
          <span style="font-size:0.875rem;font-weight:700;color:var(--text);">#<?php echo str_pad($booking_id, 5, '0', STR_PAD_LEFT); ?></span>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;">
          <span style="font-size:0.82rem;color:var(--muted);">Transaction ID</span>
          <span style="font-size:0.8rem;font-weight:600;color:var(--teal-d);"><?php echo $transaction_id; ?></span>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;">
          <span style="font-size:0.82rem;color:var(--muted);">Service</span>
          <span style="font-size:0.875rem;font-weight:600;"><?php echo htmlspecialchars($service['service_name']); ?></span>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;">
          <span style="font-size:0.82rem;color:var(--muted);">Provider</span>
          <span style="font-size:0.875rem;"><?php echo htmlspecialchars($service['provider_name']); ?></span>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;">
          <span style="font-size:0.82rem;color:var(--muted);">Payment Method</span>
          <span style="font-size:0.875rem;"><?php echo htmlspecialchars($payment_method); ?></span>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;">
          <span style="font-size:0.82rem;color:var(--muted);">Date & Time</span>
          <span style="font-size:0.875rem;"><?php echo date('d M Y, h:i A'); ?></span>
        </div>

        <div style="border-top:1px solid var(--border);margin-top:0.25rem;padding-top:0.75rem;display:flex;justify-content:space-between;align-items:center;">
          <span style="font-size:0.95rem;font-weight:700;">Amount Paid</span>
          <span style="font-size:1.25rem;font-weight:900;color:var(--teal-d);">Rs. <?php echo number_format($amount, 2); ?></span>
        </div>

      </div>
    </div>

    <!-- Status badge -->
    <div style="display:inline-flex;align-items:center;gap:0.5rem;background:#FEF3C7;color:#92400E;padding:0.5rem 1rem;border-radius:999px;font-size:0.82rem;font-weight:600;margin-bottom:2rem;">
      ⏳ Awaiting Admin Approval
    </div>

    <!-- Action buttons -->
    <div style="display:flex;flex-direction:column;gap:0.75rem;">
      <a href="my_bookings.php" class="btn btn-primary btn-lg btn-block">View My Bookings</a>
      <a href="services.php"    class="btn btn-outline btn-lg btn-block">Book Another Service</a>
    </div>

  </div>

  <!-- What happens next -->
  <div class="form-card" style="margin-top:1.25rem;">
    <div style="font-size:0.9rem;font-weight:700;color:var(--text);margin-bottom:1rem;">What happens next?</div>
    <div style="display:flex;flex-direction:column;gap:0.85rem;">
      <div style="display:flex;gap:0.85rem;align-items:flex-start;">
        <div style="width:28px;height:28px;border-radius:50%;background:var(--teal-glow);border:2px solid var(--teal);display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:800;color:var(--teal);flex-shrink:0;">1</div>
        <div>
          <div style="font-size:0.85rem;font-weight:600;color:var(--text);">Admin reviews your booking</div>
          <div style="font-size:0.78rem;color:var(--muted);">Usually within a few hours</div>
        </div>
      </div>
      <div style="display:flex;gap:0.85rem;align-items:flex-start;">
        <div style="width:28px;height:28px;border-radius:50%;background:var(--teal-glow);border:2px solid var(--teal);display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:800;color:var(--teal);flex-shrink:0;">2</div>
        <div>
          <div style="font-size:0.85rem;font-weight:600;color:var(--text);">Booking gets accepted</div>
          <div style="font-size:0.78rem;color:var(--muted);">You can check status in My Bookings</div>
        </div>
      </div>
      <div style="display:flex;gap:0.85rem;align-items:flex-start;">
        <div style="width:28px;height:28px;border-radius:50%;background:var(--teal-glow);border:2px solid var(--teal);display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:800;color:var(--teal);flex-shrink:0;">3</div>
        <div>
          <div style="font-size:0.85rem;font-weight:600;color:var(--text);">Provider comes to your location</div>
          <div style="font-size:0.78rem;color:var(--muted);">And completes the service</div>
        </div>
      </div>
    </div>
  </div>

</div>
</div>

<style>
@keyframes popIn {
  0%   { transform: scale(0); opacity: 0; }
  70%  { transform: scale(1.15); }
  100% { transform: scale(1); opacity: 1; }
}
</style>

<?php include("../includes/footer.php"); ?>
