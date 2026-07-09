<?php
session_start();
if(!isset($_SESSION['id']) || $_SESSION['role'] != 'provider') { header("Location: ../login.php"); exit(); }
include("../config/db.php");
include("../includes/header.php");

$pid = $_SESSION['id'];
$total_services = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM services WHERE provider_id='$pid'"))[0];
$total_bookings = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM bookings JOIN services ON bookings.service_id=services.id WHERE services.provider_id='$pid'"))[0];
$pending        = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM bookings JOIN services ON bookings.service_id=services.id WHERE services.provider_id='$pid' AND bookings.status='Pending'"))[0];
?>

<div class="dash-wrap">
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Menu</div>
      <a href="dashboard.php"  class="sidebar-link active"><span class="icon">🏠</span> Dashboard</a>
      <a href="add_service.php"class="sidebar-link"><span class="icon">➕</span> Add Service</a>
      <a href="bookings.php"   class="sidebar-link"><span class="icon">📋</span> My Bookings</a>
      <a href="profile.php"    class="sidebar-link"><span class="icon">👤</span> My Profile</a>
    </div>
    <a href="../logout.php" class="sidebar-link" style="margin-top:1rem;"><span class="icon">🚪</span> Sign Out</a>
  </aside>

  <main class="dash-main">
    <div class="dash-header">
      <h1>Provider Dashboard</h1>
      <p>Welcome back, <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong> 👋</p>
    </div>

    <div class="kpi-grid">
      <div class="kpi-card">
        <div class="kpi-label">My Services</div>
        <div class="kpi-value"><?php echo $total_services; ?></div>
        <div class="kpi-sub">Listed services</div>
      </div>
      <div class="kpi-card kpi-teal">
        <div class="kpi-label">Total Bookings</div>
        <div class="kpi-value"><?php echo $total_bookings; ?></div>
      </div>
      <div class="kpi-card kpi-amber">
        <div class="kpi-label">Pending</div>
        <div class="kpi-value"><?php echo $pending; ?></div>
        <div class="kpi-sub">Awaiting action</div>
      </div>
    </div>

    <div class="quick-grid">
      <a href="add_service.php" class="quick-card"><div class="quick-card-icon">➕</div><div class="quick-card-title">Add Service</div><div class="quick-card-desc">List a new service you offer</div><div class="quick-card-arrow">→</div></a>
      <a href="bookings.php"    class="quick-card"><div class="quick-card-icon">📋</div><div class="quick-card-title">Manage Bookings</div><div class="quick-card-desc">Accept, complete, or cancel requests</div><div class="quick-card-arrow">→</div></a>
      <a href="earnings.php"    class="quick-card"><div class="quick-card-icon">💰</div><div class="quick-card-title">My Earnings</div><div class="quick-card-desc">Track your income</div><div class="quick-card-arrow">→</div></a>
      <a href="profile.php"     class="quick-card"><div class="quick-card-icon">👤</div><div class="quick-card-title">My Profile</div><div class="quick-card-desc">Update your account details</div><div class="quick-card-arrow">→</div></a>
    </div>
  </main>
</div>

<?php include("../includes/footer.php"); ?>
