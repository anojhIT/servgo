<?php
session_start();
include("../config/db.php");
if(!isset($_SESSION['id']) || $_SESSION['role'] != 'provider') {
    header("Location: ../login.php"); exit();
}

$provider_id = $_SESSION['id'];

$total     = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM bookings JOIN services ON bookings.service_id=services.id WHERE services.provider_id='$provider_id'"))[0];
$pending   = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM bookings JOIN services ON bookings.service_id=services.id WHERE services.provider_id='$provider_id' AND bookings.status='Pending'"))[0];
$accepted  = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM bookings JOIN services ON bookings.service_id=services.id WHERE services.provider_id='$provider_id' AND bookings.status='Accepted'"))[0];
$completed = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM bookings JOIN services ON bookings.service_id=services.id WHERE services.provider_id='$provider_id' AND bookings.status='Completed'"))[0];

$filter     = isset($_GET['filter']) ? $_GET['filter'] : '';
$filter_sql = $filter ? "AND bookings.status='$filter'" : '';

$sql = "SELECT bookings.id, bookings.status, bookings.booking_date,
               services.service_name, services.price,
               users.name AS customer_name, users.email AS customer_email
        FROM bookings
        JOIN services ON bookings.service_id = services.id
        JOIN users    ON bookings.user_id    = users.id
        WHERE services.provider_id = '$provider_id'
        $filter_sql
        ORDER BY bookings.id DESC";
$result = mysqli_query($conn, $sql);

include("../includes/header.php");
?>

<!-- Modern Confirm Modal -->
<div id="confirm-modal" style="
  display:none; position:fixed; inset:0; z-index:9999;
  background:rgba(10,10,20,0.6); backdrop-filter:blur(6px);
  align-items:center; justify-content:center;
">
  <div style="
    background:#fff; border-radius:20px; padding:2.5rem 2rem;
    max-width:380px; width:90%; text-align:center;
    box-shadow:0 32px 80px rgba(0,0,0,0.35);
    animation: popIn 0.25s cubic-bezier(.22,1,.36,1);
  ">
    <div id="modal-icon" style="font-size:2.5rem;margin-bottom:1rem;">✅</div>
    <div id="modal-title" style="font-size:1.15rem;font-weight:800;color:var(--text);margin-bottom:0.5rem;letter-spacing:-0.02em;"></div>
    <div id="modal-desc"  style="font-size:0.875rem;color:var(--muted);margin-bottom:1.75rem;line-height:1.6;"></div>
    <div style="display:flex;gap:0.75rem;">
      <button onclick="closeModal()"
        style="flex:1;padding:0.75rem;border-radius:10px;border:1.5px solid var(--border);
               background:#fff;font-family:inherit;font-size:0.875rem;font-weight:600;
               color:var(--muted);cursor:pointer;transition:all 0.18s;"
        onmouseover="this.style.borderColor='var(--teal)';this.style.color='var(--teal)'"
        onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--muted)'">
        Cancel
      </button>
      <a id="modal-confirm-btn" href="#"
        style="flex:1;padding:0.75rem;border-radius:10px;border:none;
               font-family:inherit;font-size:0.875rem;font-weight:700;
               cursor:pointer;text-decoration:none;display:flex;
               align-items:center;justify-content:center;transition:all 0.18s;">
        Confirm
      </a>
    </div>
  </div>
</div>

<style>
@keyframes popIn {
  0%   { transform:scale(0.85); opacity:0; }
  100% { transform:scale(1);    opacity:1; }
}
</style>

<div class="dash-wrap">
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Menu</div>
      <a href="dashboard.php"   class="sidebar-link"><span class="icon">🏠</span> Dashboard</a>
      <a href="add_service.php" class="sidebar-link"><span class="icon">➕</span> Add Service</a>
      <a href="bookings.php"    class="sidebar-link active"><span class="icon">📋</span> My Bookings</a>
      <a href="earnings.php"    class="sidebar-link"><span class="icon">💰</span> Earnings</a>
      <a href="profile.php"     class="sidebar-link"><span class="icon">👤</span> My Profile</a>
    </div>
    <a href="../logout.php" class="sidebar-link" style="margin-top:1rem;"><span class="icon">🚪</span> Sign Out</a>
  </aside>

  <main class="dash-main">

    <?php if(isset($_GET['msg'])): ?>
      <div class="alert <?php echo $_GET['msg']=='error' ? 'alert-error' : 'alert-success'; ?>">
        <?php echo $_GET['msg']=='error' ? '⚠️ Something went wrong.' : '✅ ' . htmlspecialchars($_GET['msg']); ?>
      </div>
    <?php endif; ?>

    <div class="dash-header">
      <h1>My Bookings</h1>
      <p>Manage all customer requests for your services</p>
    </div>

    <!-- KPI Cards -->
    <div class="kpi-grid" style="margin-bottom:1.5rem;">
      <div class="kpi-card">
        <div class="kpi-label">Total Requests</div>
        <div class="kpi-value"><?php echo $total; ?></div>
        <div class="kpi-sub">All time</div>
      </div>
      <div class="kpi-card kpi-amber">
        <div class="kpi-label">Pending</div>
        <div class="kpi-value"><?php echo $pending; ?></div>
        <div class="kpi-sub">Needs your action</div>
      </div>
      <div class="kpi-card kpi-teal">
        <div class="kpi-label">Accepted</div>
        <div class="kpi-value"><?php echo $accepted; ?></div>
        <div class="kpi-sub">In progress</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Completed</div>
        <div class="kpi-value"><?php echo $completed; ?></div>
        <div class="kpi-sub">Finished jobs</div>
      </div>
    </div>

    <!-- Filter Tabs -->
    <div style="display:flex;gap:0.5rem;margin-bottom:1.25rem;flex-wrap:wrap;">
      <?php
        $tabs = [
          ''          => 'All Bookings',
          'Pending'   => '⏳ Pending',
          'Accepted'  => '✅ Accepted',
          'Completed' => '🏁 Completed',
          'Cancelled' => '❌ Cancelled',
        ];
        foreach($tabs as $val => $label):
          $active = ($filter === $val)
            ? 'background:var(--teal);color:var(--dark);border:1.5px solid var(--teal);'
            : 'background:#fff;color:var(--muted);border:1.5px solid var(--border);';
      ?>
        <a href="bookings.php<?php echo $val ? '?filter='.$val : ''; ?>"
           style="<?php echo $active; ?> padding:0.45rem 1rem;border-radius:999px;font-size:0.82rem;font-weight:600;text-decoration:none;transition:all 0.18s;display:inline-flex;align-items:center;gap:0.4rem;">
          <?php echo $label; ?>
          <?php if($val == 'Pending' && $pending > 0): ?>
            <span style="background:#EF4444;color:#fff;border-radius:999px;padding:0.1rem 0.45rem;font-size:0.7rem;"><?php echo $pending; ?></span>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Table -->
    <div class="table-card">
      <div class="table-card-header">
        <span class="table-card-title">
          <?php echo $filter ? $filter . ' Bookings' : 'All Booking Requests'; ?>
        </span>
        <a href="earnings.php" class="btn btn-primary btn-sm">💰 View Earnings</a>
      </div>

      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Customer</th>
            <th>Service</th>
            <th>Date</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php
        $has = false;
        while($row = mysqli_fetch_assoc($result)):
          $has = true;
          $s   = strtolower($row['status']);
          $map = ['pending'=>'badge-pending','accepted'=>'badge-accepted','completed'=>'badge-completed','cancelled'=>'badge-cancelled','declined'=>'badge-declined'];
          $cls = $map[$s] ?? 'badge-pending';
        ?>
          <tr>
            <td class="td-muted">#<?php echo str_pad($row['id'],5,'0',STR_PAD_LEFT); ?></td>
            <td>
              <div style="font-weight:600;font-size:0.875rem;"><?php echo htmlspecialchars($row['customer_name']); ?></div>
              <div style="font-size:0.75rem;color:var(--muted);"><?php echo htmlspecialchars($row['customer_email']); ?></div>
            </td>
            <td><?php echo htmlspecialchars($row['service_name']); ?></td>
            <td class="td-muted"><?php echo date('d M Y', strtotime($row['booking_date'])); ?></td>
            <td><strong style="color:var(--teal-d);">Rs. <?php echo number_format($row['price'],2); ?></strong></td>
            <td><span class="badge <?php echo $cls; ?>"><?php echo $row['status']; ?></span></td>
            <td>
              <div class="td-actions">
                <?php if($row['status'] == 'Pending'): ?>
                  <button onclick="showModal(
                    'update_booking.php?id=<?php echo $row['id']; ?>&status=Accepted',
                    '✅','Accept Booking',
                    'Are you sure you want to accept this booking from <?php echo htmlspecialchars($row["customer_name"]); ?>?',
                    'var(--teal)','var(--dark)','Accept'
                  )" class="btn btn-success btn-sm">✅ Accept</button>

                  <button onclick="showModal(
                    'update_booking.php?id=<?php echo $row['id']; ?>&status=Cancelled',
                    '❌','Cancel Booking',
                    'Are you sure you want to cancel this booking?',
                    '#EF4444','#fff','Yes, Cancel'
                  )" class="btn btn-danger btn-sm">✕ Cancel</button>

                <?php elseif($row['status'] == 'Accepted'): ?>
                  <button onclick="showModal(
                    'update_booking.php?id=<?php echo $row['id']; ?>&status=Completed',
                    '🏁','Mark as Completed',
                    'Confirm that you have completed the service for <?php echo htmlspecialchars($row["customer_name"]); ?>?',
                    'var(--teal)','var(--dark)','Yes, Complete'
                  )" class="btn btn-primary btn-sm">🏁 Complete</button>

                  <button onclick="showModal(
                    'update_booking.php?id=<?php echo $row['id']; ?>&status=Cancelled',
                    '❌','Cancel Booking',
                    'Are you sure you want to cancel this accepted booking?',
                    '#EF4444','#fff','Yes, Cancel'
                  )" class="btn btn-danger btn-sm">✕ Cancel</button>

                <?php elseif($row['status'] == 'Completed'): ?>
                  <span style="color:var(--teal-d);font-size:0.82rem;font-weight:600;">✅ Done</span>

                <?php else: ?>
                  <span class="td-muted">—</span>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endwhile; ?>
        <?php if(!$has): ?>
          <tr><td colspan="7">
            <div class="empty-state">
              <div class="empty-icon">📋</div>
              <p>No <?php echo strtolower($filter); ?> bookings found.</p>
            </div>
          </td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<script>
function showModal(url, icon, title, desc, btnBg, btnColor, btnText) {
  document.getElementById('modal-icon').textContent         = icon;
  document.getElementById('modal-title').textContent        = title;
  document.getElementById('modal-desc').textContent         = desc;
  var btn = document.getElementById('modal-confirm-btn');
  btn.href                = url;
  btn.style.background    = btnBg;
  btn.style.color         = btnColor;
  btn.textContent         = btnText;
  var modal = document.getElementById('confirm-modal');
  modal.style.display     = 'flex';
}

function closeModal() {
  document.getElementById('confirm-modal').style.display = 'none';
}

/* Close if clicked outside */
document.getElementById('confirm-modal').addEventListener('click', function(e) {
  if(e.target === this) closeModal();
});

/* Close on Escape key */
document.addEventListener('keydown', function(e) {
  if(e.key === 'Escape') closeModal();
});
</script>

<?php include("../includes/footer.php"); ?>
