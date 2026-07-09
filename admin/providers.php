<?php
session_start();
include("../config/db.php");
if(!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') { header("Location: ../login.php"); exit(); }
$result = mysqli_query($conn,"SELECT * FROM users WHERE role='provider' ORDER BY id DESC");
include("../includes/header.php");
?>

<div class="dash-wrap">
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Main</div>
      <a href="dashboard.php" class="sidebar-link"><span class="icon">🏠</span> Dashboard</a>
      <a href="bookings.php"  class="sidebar-link"><span class="icon">📋</span> Bookings</a>
      <a href="services.php"  class="sidebar-link"><span class="icon">🔧</span> Services</a>
      <a href="reviews.php"   class="sidebar-link"><span class="icon">⭐</span> Reviews</a>
    </div>
    <div class="sidebar-section">
      <div class="sidebar-label">People</div>
      <a href="users.php"     class="sidebar-link"><span class="icon">👤</span> Users</a>
      <a href="providers.php" class="sidebar-link active"><span class="icon">🔨</span> Providers</a>
    </div>
    <a href="../logout.php" class="sidebar-link" style="margin-top:1rem;"><span class="icon">🚪</span> Sign Out</a>
  </aside>

  <main class="dash-main">
    <div class="dash-header">
      <h1>Service Providers</h1>
      <p>All verified professionals on the platform</p>
    </div>

    <div class="table-card">
      <div class="table-card-header">
        <span class="table-card-title">Providers</span>
      </div>
      <table>
        <thead>
          <tr><th>#</th><th>Name</th><th>Email</th><th>Joined</th></tr>
        </thead>
        <tbody>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
          <tr>
            <td class="td-muted"><?php echo $row['id']; ?></td>
            <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
            <td class="td-muted"><?php echo htmlspecialchars($row['email']); ?></td>
            <td class="td-muted"><?php echo $row['created_at'] ?? '—'; ?></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<?php include("../includes/footer.php"); ?>
