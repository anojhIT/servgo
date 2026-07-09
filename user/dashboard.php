<?php
session_start();
if(!isset($_SESSION['id']) || $_SESSION['role'] != 'user') { header("Location: ../login.php"); exit(); }
include("../config/db.php");
include("../includes/header.php");

$uid = $_SESSION['id'];
$total_bookings  = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM bookings WHERE user_id='$uid'"))[0];
$accepted        = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM bookings WHERE user_id='$uid' AND status='Accepted'"))[0];
$pending         = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM bookings WHERE user_id='$uid' AND status='Pending'"))[0];
?>

<div class="dash-wrap">
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Menu</div>
      <a href="dashboard.php"  class="sidebar-link active"><span class="icon">🏠</span> Dashboard</a>
      <a href="services.php"   class="sidebar-link"><span class="icon">🔧</span> Browse Services</a>
      <a href="my_bookings.php"class="sidebar-link"><span class="icon">📋</span> My Bookings</a>
      <a href="reviews.php"    class="sidebar-link"><span class="icon">⭐</span> Write Review</a>
      <a href="profile.php"    class="sidebar-link"><span class="icon">👤</span> My Profile</a>
    </div>
    <a href="../logout.php" class="sidebar-link" style="margin-top:1rem;"><span class="icon">🚪</span> Sign Out</a>
  </aside>

  <main class="dash-main">
    <div class="dash-header">
      <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?> 👋</h1>
      <p>Here's what's happening with your bookings today.</p>
    </div>

    <div class="kpi-grid">
      <div class="kpi-card">
        <div class="kpi-label">Total Bookings</div>
        <div class="kpi-value"><?php echo $total_bookings; ?></div>
      </div>
      <div class="kpi-card kpi-teal">
        <div class="kpi-label">Accepted</div>
        <div class="kpi-value"><?php echo $accepted; ?></div>
      </div>
      <div class="kpi-card kpi-amber">
        <div class="kpi-label">Pending</div>
        <div class="kpi-value"><?php echo $pending; ?></div>
      </div>
    </div>

    <div class="quick-grid">
      <a href="services.php"    class="quick-card"><div class="quick-card-icon">🔧</div><div class="quick-card-title">Browse Services</div><div class="quick-card-desc">Find and book a professional</div><div class="quick-card-arrow">→</div></a>
      <a href="my_bookings.php" class="quick-card"><div class="quick-card-icon">📋</div><div class="quick-card-title">My Bookings</div><div class="quick-card-desc">Track your service requests</div><div class="quick-card-arrow">→</div></a>
      <a href="reviews.php"     class="quick-card"><div class="quick-card-icon">⭐</div><div class="quick-card-title">Write a Review</div><div class="quick-card-desc">Share your experience</div><div class="quick-card-arrow">→</div></a>
      <a href="profile.php"     class="quick-card"><div class="quick-card-icon">👤</div><div class="quick-card-title">My Profile</div><div class="quick-card-desc">Update your details</div><div class="quick-card-arrow">→</div></a>
    </div>
  </main>
</div>

<?php include("../includes/footer.php"); ?>
