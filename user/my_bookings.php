<?php
session_start();
include("../config/db.php");
if(!isset($_SESSION['id']) || $_SESSION['role'] != 'user') { header("Location: ../login.php"); exit(); }

$user_id = $_SESSION['id'];

/* Cancel booking */
if(isset($_GET['cancel'])) {
    $bid = mysqli_real_escape_string($conn, $_GET['cancel']);
    mysqli_query($conn, "UPDATE bookings SET status='Cancelled' WHERE id='$bid' AND user_id='$user_id' AND status='Pending'");
    header("Location: my_bookings.php?msg=Booking+cancelled+successfully!");
    exit();
}

$filter     = isset($_GET['filter']) ? $_GET['filter'] : '';
$filter_sql = $filter ? "AND bookings.status='$filter'" : '';

$sql = "SELECT bookings.*, services.service_name, services.price
        FROM bookings
        JOIN services ON bookings.service_id=services.id
        WHERE bookings.user_id='$user_id' $filter_sql
        ORDER BY bookings.id DESC";
$result = mysqli_query($conn,$sql);

$total     = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM bookings WHERE user_id='$user_id'"))[0];
$pending   = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM bookings WHERE user_id='$user_id' AND status='Pending'"))[0];
$accepted  = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM bookings WHERE user_id='$user_id' AND status='Accepted'"))[0];
$completed = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM bookings WHERE user_id='$user_id' AND status='Completed'"))[0];

include("../includes/header.php");
?>

<!-- Cancel Modal -->
<div id="cancel-modal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(10,10,20,0.6);backdrop-filter:blur(6px);align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:20px;padding:2.5rem 2rem;max-width:380px;width:90%;text-align:center;box-shadow:0 32px 80px rgba(0,0,0,0.35);animation:popIn 0.25s cubic-bezier(.22,1,.36,1);">
    <div style="font-size:2.5rem;margin-bottom:1rem;">❌</div>
    <div style="font-size:1.15rem;font-weight:800;color:var(--text);margin-bottom:0.5rem;">Cancel Booking</div>
    <div style="font-size:0.875rem;color:var(--muted);margin-bottom:1.75rem;line-height:1.6;">Are you sure you want to cancel this booking? This cannot be undone.</div>
    <div style="display:flex;gap:0.75rem;">
      <button onclick="document.getElementById('cancel-modal').style.display='none'" style="flex:1;padding:0.75rem;border-radius:10px;border:1.5px solid var(--border);background:#fff;font-family:inherit;font-size:0.875rem;font-weight:600;color:var(--muted);cursor:pointer;">Keep Booking</button>
      <a id="cancel-confirm-btn" href="#" style="flex:1;padding:0.75rem;border-radius:10px;background:#EF4444;color:#fff;font-family:inherit;font-size:0.875rem;font-weight:700;cursor:pointer;text-decoration:none;display:flex;align-items:center;justify-content:center;">Yes, Cancel</a>
    </div>
  </div>
</div>
<style>@keyframes popIn{0%{transform:scale(0.85);opacity:0}100%{transform:scale(1);opacity:1}}</style>

<div class="dash-wrap">
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Menu</div>
      <a href="dashboard.php"   class="sidebar-link"><span class="icon">🏠</span> Dashboard</a>
      <a href="services.php"    class="sidebar-link"><span class="icon">🔧</span> Browse Services</a>
      <a href="my_bookings.php" class="sidebar-link active"><span class="icon">📋</span> My Bookings</a>
      <a href="reviews.php"     class="sidebar-link"><span class="icon">⭐</span> Write Review</a>
      <a href="profile.php"     class="sidebar-link"><span class="icon">👤</span> My Profile</a>
    </div>
    <a href="../logout.php" class="sidebar-link" style="margin-top:1rem;"><span class="icon">🚪</span> Sign Out</a>
  </aside>

  <main class="dash-main">
    <?php if(isset($_GET['msg'])): ?>
      <div class="alert alert-success">✅ <?php echo htmlspecialchars($_GET['msg']); ?></div>
    <?php endif; ?>

    <div class="dash-header">
      <h1>My Bookings</h1>
      <p>All your service requests and their current status</p>
    </div>

    <!-- KPIs -->
    <div class="kpi-grid" style="margin-bottom:1.5rem;">
      <div class="kpi-card"><div class="kpi-label">Total</div><div class="kpi-value"><?php echo $total; ?></div></div>
      <div class="kpi-card kpi-amber"><div class="kpi-label">Pending</div><div class="kpi-value"><?php echo $pending; ?></div></div>
      <div class="kpi-card kpi-teal"><div class="kpi-label">Accepted</div><div class="kpi-value"><?php echo $accepted; ?></div></div>
      <div class="kpi-card"><div class="kpi-label">Completed</div><div class="kpi-value"><?php echo $completed; ?></div></div>
    </div>

    <!-- Filter Tabs -->
    <div style="display:flex;gap:0.5rem;margin-bottom:1.25rem;flex-wrap:wrap;">
      <?php
        $tabs=[''=>'All','Pending'=>'⏳ Pending','Accepted'=>'✅ Accepted','Completed'=>'🏁 Completed','Cancelled'=>'❌ Cancelled'];
        foreach($tabs as $val=>$label):
          $act=($filter===$val)?'background:var(--teal);color:var(--dark);border:1.5px solid var(--teal);':'background:#fff;color:var(--muted);border:1.5px solid var(--border);';
      ?>
        <a href="my_bookings.php<?php echo $val?'?filter='.$val:''; ?>"
           style="<?php echo $act; ?>padding:0.45rem 1rem;border-radius:999px;font-size:0.82rem;font-weight:600;text-decoration:none;">
          <?php echo $label; ?>
        </a>
      <?php endforeach; ?>
    </div>

    <div class="table-card">
      <div class="table-card-header">
        <span class="table-card-title">Booking History</span>
        <a href="services.php" class="btn btn-primary btn-sm">+ New Booking</a>
      </div>
      <table>
        <thead>
          <tr><th>#</th><th>Service</th><th>Date</th><th>Amount</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php
        $has=false;
        while($row=mysqli_fetch_assoc($result)):
          $has=true;
          $s=strtolower($row['status']);
          $map=['pending'=>'badge-pending','accepted'=>'badge-accepted','declined'=>'badge-declined','completed'=>'badge-completed','cancelled'=>'badge-cancelled'];
          $cls=$map[$s]??'badge-pending';
        ?>
          <tr>
            <td class="td-muted">#<?php echo str_pad($row['id'],5,'0',STR_PAD_LEFT); ?></td>
            <td><strong><?php echo htmlspecialchars($row['service_name']); ?></strong></td>
            <td class="td-muted"><?php echo date('d M Y', strtotime($row['booking_date'])); ?></td>
            <td><strong style="color:var(--teal-d);">Rs. <?php echo number_format($row['price'],2); ?></strong></td>
            <td><span class="badge <?php echo $cls; ?>"><?php echo $row['status']; ?></span></td>
            <td>
              <div class="td-actions">
                <a href="receipt.php?booking_id=<?php echo $row['id']; ?>" class="btn btn-outline btn-sm" target="_blank">🖨️ Receipt</a>
                <?php if($row['status']=='Pending'): ?>
                  <button onclick="document.getElementById('cancel-confirm-btn').href='my_bookings.php?cancel=<?php echo $row['id']; ?>';document.getElementById('cancel-modal').style.display='flex';"
                    class="btn btn-danger btn-sm">✕ Cancel</button>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endwhile; ?>
        <?php if(!$has): ?>
          <tr><td colspan="6">
            <div class="empty-state">
              <div class="empty-icon">📋</div>
              <p>No bookings yet. <a href="services.php" class="back-link">Browse services →</a></p>
            </div>
          </td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<?php include("../includes/footer.php"); ?>
