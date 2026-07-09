<?php
session_start();
if(!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php"); exit();
}
include("../includes/header.php");
include("../config/db.php");

$total_users     = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM users WHERE role='user'"))[0];
$total_providers = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM users WHERE role='provider'"))[0];
$total_bookings  = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM bookings"))[0];
$pending         = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM bookings WHERE status='Pending'"))[0];
$accepted        = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM bookings WHERE status='Accepted'"))[0];
$declined        = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM bookings WHERE status='Declined'"))[0];
$completed       = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM bookings WHERE status='Completed'"))[0];

/* Bookings per month (last 6 months) */
$months_labels = [];
$months_data   = [];
for($i = 5; $i >= 0; $i--) {
    $month_label = date('M Y', strtotime("-$i months"));
    $month_num   = date('Y-m', strtotime("-$i months"));
    $count = mysqli_fetch_row(mysqli_query($conn,
        "SELECT COUNT(*) FROM bookings WHERE DATE_FORMAT(booking_date,'%Y-%m')='$month_num'"
    ))[0];
    $months_labels[] = $month_label;
    $months_data[]   = (int)$count;
}

/* Bookings by status for doughnut */
$status_data = [$pending, $accepted, $declined, $completed];

/* Top services */
$top_services = mysqli_query($conn,
    "SELECT services.service_name, COUNT(bookings.id) AS total
     FROM bookings
     JOIN services ON bookings.service_id = services.id
     GROUP BY services.id
     ORDER BY total DESC
     LIMIT 5"
);
?>

<div class="dash-wrap">
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Main</div>
      <a href="dashboard.php" class="sidebar-link active"><span class="icon">🏠</span> Dashboard</a>
      <a href="bookings.php"  class="sidebar-link"><span class="icon">📋</span> Bookings</a>
      <a href="services.php"  class="sidebar-link"><span class="icon">🔧</span> Services</a>
      <a href="reviews.php"   class="sidebar-link"><span class="icon">⭐</span> Reviews</a>
    </div>
    <div class="sidebar-section">
      <div class="sidebar-label">People</div>
      <a href="users.php"     class="sidebar-link"><span class="icon">👤</span> Users</a>
      <a href="providers.php" class="sidebar-link"><span class="icon">🔨</span> Providers</a>
    </div>
    <a href="../logout.php" class="sidebar-link" style="margin-top:1rem;"><span class="icon">🚪</span> Sign Out</a>
  </aside>

  <main class="dash-main">
    <div class="dash-header">
      <h1>Admin Dashboard</h1>
      <p>Welcome back, <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong> — here's what's happening today.</p>
    </div>

    <!-- KPI Cards -->
    <div class="kpi-grid">
      <div class="kpi-card">
        <div class="kpi-label">Total Users</div>
        <div class="kpi-value"><?php echo $total_users; ?></div>
        <div class="kpi-sub">Registered customers</div>
      </div>
      <div class="kpi-card kpi-teal">
        <div class="kpi-label">Providers</div>
        <div class="kpi-value"><?php echo $total_providers; ?></div>
        <div class="kpi-sub">Active service providers</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Total Bookings</div>
        <div class="kpi-value"><?php echo $total_bookings; ?></div>
        <div class="kpi-sub">All time</div>
      </div>
      <div class="kpi-card kpi-amber">
        <div class="kpi-label">Pending Approval</div>
        <div class="kpi-value"><?php echo $pending; ?></div>
        <div class="kpi-sub">Awaiting your action</div>
      </div>
    </div>

<?php
$total_revenue = mysqli_fetch_row(mysqli_query($conn,"SELECT COALESCE(SUM(services.price),0) FROM bookings JOIN services ON bookings.service_id=services.id WHERE bookings.status='Completed'"))[0];
$pending_revenue = mysqli_fetch_row(mysqli_query($conn,"SELECT COALESCE(SUM(services.price),0) FROM bookings JOIN services ON bookings.service_id=services.id WHERE bookings.status='Accepted'"))[0];
?>

    <!-- Revenue Cards -->
    <div class="kpi-grid" style="margin-bottom:1.25rem;">
      <div class="kpi-card kpi-teal">
        <div class="kpi-label">💰 Total Revenue</div>
        <div class="kpi-value" style="font-size:1.4rem;">Rs. <?php echo number_format($total_revenue,2); ?></div>
        <div class="kpi-sub">From completed bookings</div>
      </div>
      <div class="kpi-card kpi-amber">
        <div class="kpi-label">⏳ Pending Revenue</div>
        <div class="kpi-value" style="font-size:1.4rem;">Rs. <?php echo number_format($pending_revenue,2); ?></div>
        <div class="kpi-sub">From accepted bookings</div>
      </div>
    </div>

    <!-- CHARTS ROW -->
    <div style="display:grid;grid-template-columns:1fr 340px;gap:1.25rem;margin-bottom:1.25rem;">

      <!-- Line/Bar Chart — Bookings per Month -->
      <div class="form-card" style="padding:1.5rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;">
          <div>
            <div style="font-size:1rem;font-weight:800;color:var(--text);">Bookings Overview</div>
            <div style="font-size:0.78rem;color:var(--muted);">Last 6 months</div>
          </div>
          <span class="badge badge-accepted">📈 Live</span>
        </div>
        <canvas id="bookingsChart" height="110"></canvas>
      </div>

      <!-- Doughnut — Booking Status -->
      <div class="form-card" style="padding:1.5rem;">
        <div style="font-size:1rem;font-weight:800;color:var(--text);margin-bottom:0.25rem;">Booking Status</div>
        <div style="font-size:0.78rem;color:var(--muted);margin-bottom:1.25rem;">All time breakdown</div>
        <canvas id="statusChart" height="160"></canvas>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;margin-top:1rem;">
          <div style="font-size:0.75rem;display:flex;align-items:center;gap:0.4rem;"><span style="width:10px;height:10px;border-radius:50%;background:#F59E0B;display:inline-block;"></span> Pending: <?php echo $pending; ?></div>
          <div style="font-size:0.75rem;display:flex;align-items:center;gap:0.4rem;"><span style="width:10px;height:10px;border-radius:50%;background:#00C9A7;display:inline-block;"></span> Accepted: <?php echo $accepted; ?></div>
          <div style="font-size:0.75rem;display:flex;align-items:center;gap:0.4rem;"><span style="width:10px;height:10px;border-radius:50%;background:#EF4444;display:inline-block;"></span> Declined: <?php echo $declined; ?></div>
          <div style="font-size:0.75rem;display:flex;align-items:center;gap:0.4rem;"><span style="width:10px;height:10px;border-radius:50%;background:#3B82F6;display:inline-block;"></span> Completed: <?php echo $completed; ?></div>
        </div>
      </div>

    </div>

    <!-- Top Services + Quick Access Row -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.25rem;">

      <!-- Top Services Bar Chart -->
      <div class="form-card" style="padding:1.5rem;">
        <div style="font-size:1rem;font-weight:800;color:var(--text);margin-bottom:0.25rem;">Top Services</div>
        <div style="font-size:0.78rem;color:var(--muted);margin-bottom:1.25rem;">By number of bookings</div>
        <canvas id="topServicesChart" height="160"></canvas>
      </div>

      <!-- Quick Access -->
      <div class="form-card" style="padding:1.5rem;">
        <div style="font-size:1rem;font-weight:800;color:var(--text);margin-bottom:1.25rem;">Quick Access</div>
        <div style="display:flex;flex-direction:column;gap:0.6rem;">
          <a href="users.php"     class="quick-card" style="flex-direction:row;align-items:center;padding:0.85rem 1rem;gap:0.75rem;">
            <div class="quick-card-icon" style="font-size:1.2rem;">👤</div>
            <div><div class="quick-card-title" style="font-size:0.875rem;">Users</div><div class="quick-card-desc" style="font-size:0.78rem;">Manage customers</div></div>
            <div class="quick-card-arrow" style="margin-left:auto;">→</div>
          </a>
          <a href="providers.php" class="quick-card" style="flex-direction:row;align-items:center;padding:0.85rem 1rem;gap:0.75rem;">
            <div class="quick-card-icon" style="font-size:1.2rem;">🔨</div>
            <div><div class="quick-card-title" style="font-size:0.875rem;">Providers</div><div class="quick-card-desc" style="font-size:0.78rem;">Manage providers</div></div>
            <div class="quick-card-arrow" style="margin-left:auto;">→</div>
          </a>
          <a href="bookings.php"  class="quick-card" style="flex-direction:row;align-items:center;padding:0.85rem 1rem;gap:0.75rem;">
            <div class="quick-card-icon" style="font-size:1.2rem;">📋</div>
            <div><div class="quick-card-title" style="font-size:0.875rem;">Bookings</div><div class="quick-card-desc" style="font-size:0.78rem;">Approve or decline</div></div>
            <div class="quick-card-arrow" style="margin-left:auto;">→</div>
          </a>
          <a href="reviews.php"   class="quick-card" style="flex-direction:row;align-items:center;padding:0.85rem 1rem;gap:0.75rem;">
            <div class="quick-card-icon" style="font-size:1.2rem;">⭐</div>
            <div><div class="quick-card-title" style="font-size:0.875rem;">Reviews</div><div class="quick-card-desc" style="font-size:0.78rem;">Customer feedback</div></div>
            <div class="quick-card-arrow" style="margin-left:auto;">→</div>
          </a>
        </div>
      </div>

    </div>

  </main>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
/* PHP data → JS */
var monthLabels  = <?php echo json_encode($months_labels); ?>;
var monthData    = <?php echo json_encode($months_data); ?>;
var statusData   = <?php echo json_encode($status_data); ?>;

<?php
$top_svc_labels = [];
$top_svc_data   = [];
while($row = mysqli_fetch_assoc($top_services)) {
    $top_svc_labels[] = $row['service_name'];
    $top_svc_data[]   = (int)$row['total'];
}
?>
var topSvcLabels = <?php echo json_encode($top_svc_labels); ?>;
var topSvcData   = <?php echo json_encode($top_svc_data); ?>;

/* Shared defaults */
Chart.defaults.font.family = 'Inter, system-ui, sans-serif';
Chart.defaults.color       = '#6B7280';

/* 1. Bookings per month — Bar chart */
new Chart(document.getElementById('bookingsChart'), {
  type: 'bar',
  data: {
    labels: monthLabels,
    datasets: [{
      label: 'Bookings',
      data: monthData,
      backgroundColor: 'rgba(0,201,167,0.15)',
      borderColor: '#00C9A7',
      borderWidth: 2,
      borderRadius: 6,
      borderSkipped: false,
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: function(ctx) { return ' ' + ctx.parsed.y + ' bookings'; }
        }
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: { stepSize: 1 },
        grid: { color: 'rgba(0,0,0,0.04)' }
      },
      x: { grid: { display: false } }
    }
  }
});

/* 2. Status doughnut */
new Chart(document.getElementById('statusChart'), {
  type: 'doughnut',
  data: {
    labels: ['Pending', 'Accepted', 'Declined', 'Completed'],
    datasets: [{
      data: statusData,
      backgroundColor: ['#FEF3C7', '#D1FAE5', '#FEE2E2', '#DBEAFE'],
      borderColor:     ['#F59E0B', '#00C9A7', '#EF4444', '#3B82F6'],
      borderWidth: 2,
    }]
  },
  options: {
    responsive: true,
    cutout: '68%',
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: function(ctx) { return ' ' + ctx.label + ': ' + ctx.parsed; }
        }
      }
    }
  }
});

/* 3. Top services horizontal bar */
new Chart(document.getElementById('topServicesChart'), {
  type: 'bar',
  data: {
    labels: topSvcLabels.length ? topSvcLabels : ['No data yet'],
    datasets: [{
      label: 'Bookings',
      data: topSvcData.length ? topSvcData : [0],
      backgroundColor: [
        'rgba(0,201,167,0.2)',
        'rgba(255,107,53,0.2)',
        'rgba(59,130,246,0.2)',
        'rgba(245,158,11,0.2)',
        'rgba(139,92,246,0.2)',
      ],
      borderColor: ['#00C9A7','#FF6B35','#3B82F6','#F59E0B','#8B5CF6'],
      borderWidth: 2,
      borderRadius: 6,
    }]
  },
  options: {
    indexAxis: 'y',
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      x: {
        beginAtZero: true,
        ticks: { stepSize: 1 },
        grid: { color: 'rgba(0,0,0,0.04)' }
      },
      y: { grid: { display: false } }
    }
  }
});
</script>

<?php include("../includes/footer.php"); ?>
