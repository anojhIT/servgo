<?php
session_start();
include("../config/db.php");
if(!isset($_SESSION['id']) || $_SESSION['role'] != 'provider') {
    header("Location: ../login.php"); exit();
}

$pid = $_SESSION['id'];

/* Total earned from completed bookings */
$total_earned = mysqli_fetch_row(mysqli_query($conn,
    "SELECT COALESCE(SUM(services.price), 0)
     FROM bookings
     JOIN services ON bookings.service_id = services.id
     WHERE services.provider_id = '$pid'
     AND bookings.status = 'Completed'"
))[0];

/* This month earnings */
$this_month = mysqli_fetch_row(mysqli_query($conn,
    "SELECT COALESCE(SUM(services.price), 0)
     FROM bookings
     JOIN services ON bookings.service_id = services.id
     WHERE services.provider_id = '$pid'
     AND bookings.status = 'Completed'
     AND MONTH(bookings.booking_date) = MONTH(NOW())
     AND YEAR(bookings.booking_date)  = YEAR(NOW())"
))[0];

/* Total completed bookings */
$total_jobs = mysqli_fetch_row(mysqli_query($conn,
    "SELECT COUNT(*)
     FROM bookings
     JOIN services ON bookings.service_id = services.id
     WHERE services.provider_id = '$pid'
     AND bookings.status = 'Completed'"
))[0];

/* Pending earnings (accepted but not completed) */
$pending_earned = mysqli_fetch_row(mysqli_query($conn,
    "SELECT COALESCE(SUM(services.price), 0)
     FROM bookings
     JOIN services ON bookings.service_id = services.id
     WHERE services.provider_id = '$pid'
     AND bookings.status = 'Accepted'"
))[0];

/* Earnings per month (last 6 months) */
$months_labels = [];
$months_data   = [];
for($i = 5; $i >= 0; $i--) {
    $label     = date('M Y', strtotime("-$i months"));
    $month_num = date('Y-m',  strtotime("-$i months"));
    $earned = mysqli_fetch_row(mysqli_query($conn,
        "SELECT COALESCE(SUM(services.price),0)
         FROM bookings
         JOIN services ON bookings.service_id = services.id
         WHERE services.provider_id='$pid'
         AND bookings.status='Completed'
         AND DATE_FORMAT(bookings.booking_date,'%Y-%m')='$month_num'"
    ))[0];
    $months_labels[] = $label;
    $months_data[]   = (float)$earned;
}

/* Booking history with earnings */
$history = mysqli_query($conn,
    "SELECT bookings.id, bookings.booking_date, bookings.status,
            services.service_name, services.price,
            users.name AS customer_name
     FROM bookings
     JOIN services ON bookings.service_id = services.id
     JOIN users    ON bookings.user_id    = users.id
     WHERE services.provider_id = '$pid'
     ORDER BY bookings.id DESC
     LIMIT 20"
);

include("../includes/header.php");
?>

<div class="dash-wrap">
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Menu</div>
      <a href="dashboard.php"   class="sidebar-link"><span class="icon">🏠</span> Dashboard</a>
      <a href="add_service.php" class="sidebar-link"><span class="icon">➕</span> Add Service</a>
      <a href="bookings.php"    class="sidebar-link"><span class="icon">📋</span> My Bookings</a>
      <a href="earnings.php"    class="sidebar-link active"><span class="icon">💰</span> Earnings</a>
      <a href="profile.php"     class="sidebar-link"><span class="icon">👤</span> My Profile</a>
    </div>
    <a href="../logout.php" class="sidebar-link" style="margin-top:1rem;"><span class="icon">🚪</span> Sign Out</a>
  </aside>

  <main class="dash-main">
    <div class="dash-header">
      <h1>💰 My Earnings</h1>
      <p>Track your income from completed services</p>
    </div>

    <!-- KPI Cards -->
    <div class="kpi-grid" style="grid-template-columns:repeat(4,1fr);">
      <div class="kpi-card kpi-teal">
        <div class="kpi-label">Total Earned</div>
        <div class="kpi-value" style="font-size:1.5rem;">Rs. <?php echo number_format($total_earned, 2); ?></div>
        <div class="kpi-sub">From completed jobs</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">This Month</div>
        <div class="kpi-value" style="font-size:1.5rem;">Rs. <?php echo number_format($this_month, 2); ?></div>
        <div class="kpi-sub"><?php echo date('F Y'); ?></div>
      </div>
      <div class="kpi-card kpi-amber">
        <div class="kpi-label">Pending Income</div>
        <div class="kpi-value" style="font-size:1.5rem;">Rs. <?php echo number_format($pending_earned, 2); ?></div>
        <div class="kpi-sub">Accepted, not yet completed</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Jobs Completed</div>
        <div class="kpi-value"><?php echo $total_jobs; ?></div>
        <div class="kpi-sub">Total finished services</div>
      </div>
    </div>

    <!-- Earnings Chart -->
    <div class="form-card" style="padding:1.75rem;margin-bottom:1.25rem;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;">
        <div>
          <div style="font-size:1rem;font-weight:800;color:var(--text);">Earnings Overview</div>
          <div style="font-size:0.78rem;color:var(--muted);">Last 6 months — completed jobs only</div>
        </div>
        <span class="badge badge-accepted">Rs. <?php echo number_format($total_earned,2); ?> total</span>
      </div>
      <canvas id="earningsChart" height="90"></canvas>
    </div>

    <!-- Booking History Table -->
    <div class="table-card">
      <div class="table-card-header">
        <span class="table-card-title">Recent Booking History</span>
      </div>
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Customer</th>
            <th>Service</th>
            <th>Date</th>
            <th>Status</th>
            <th>Amount</th>
          </tr>
        </thead>
        <tbody>
        <?php
        $has = false;
        while($row = mysqli_fetch_assoc($history)):
          $has = true;
          $s   = strtolower($row['status']);
          $map = ['pending'=>'badge-pending','accepted'=>'badge-accepted','completed'=>'badge-completed','declined'=>'badge-declined','cancelled'=>'badge-cancelled'];
          $cls = $map[$s] ?? 'badge-pending';
        ?>
          <tr>
            <td class="td-muted"><?php echo str_pad($row['id'],5,'0',STR_PAD_LEFT); ?></td>
            <td><strong><?php echo htmlspecialchars($row['customer_name']); ?></strong></td>
            <td><?php echo htmlspecialchars($row['service_name']); ?></td>
            <td class="td-muted"><?php echo date('d M Y', strtotime($row['booking_date'])); ?></td>
            <td><span class="badge <?php echo $cls; ?>"><?php echo $row['status']; ?></span></td>
            <td>
              <?php if($row['status'] == 'Completed'): ?>
                <strong style="color:var(--teal-d);">Rs. <?php echo number_format($row['price'],2); ?></strong>
              <?php elseif($row['status'] == 'Accepted'): ?>
                <span style="color:var(--amber);">Rs. <?php echo number_format($row['price'],2); ?> (pending)</span>
              <?php else: ?>
                <span class="td-muted">—</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
        <?php if(!$has): ?>
          <tr><td colspan="6">
            <div class="empty-state">
              <div class="empty-icon">💰</div>
              <p>No bookings yet. Add a service to start earning.</p>
            </div>
          </td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
var labels = <?php echo json_encode($months_labels); ?>;
var data   = <?php echo json_encode($months_data); ?>;

Chart.defaults.font.family = 'Inter, system-ui, sans-serif';
Chart.defaults.color       = '#6B7280';

new Chart(document.getElementById('earningsChart'), {
  type: 'line',
  data: {
    labels: labels,
    datasets: [{
      label: 'Earnings (Rs.)',
      data: data,
      borderColor: '#00C9A7',
      backgroundColor: 'rgba(0,201,167,0.08)',
      borderWidth: 2.5,
      pointBackgroundColor: '#00C9A7',
      pointRadius: 5,
      pointHoverRadius: 7,
      fill: true,
      tension: 0.4,
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: function(ctx) { return ' Rs. ' + ctx.parsed.y.toFixed(2); }
        }
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        grid: { color: 'rgba(0,0,0,0.04)' },
        ticks: {
          callback: function(val) { return 'Rs. ' + val; }
        }
      },
      x: { grid: { display: false } }
    }
  }
});
</script>

<?php include("../includes/footer.php"); ?>
