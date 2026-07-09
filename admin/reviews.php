<?php
session_start();
include("../config/db.php");
if(!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') { header("Location: ../login.php"); exit(); }

$sql = "SELECT reviews.id, reviews.rating, reviews.review,
               users.name, services.service_name
        FROM reviews
        JOIN users    ON reviews.user_id    = users.id
        JOIN services ON reviews.service_id = services.id
        ORDER BY reviews.id DESC";
$result = mysqli_query($conn,$sql);
include("../includes/header.php");
?>

<div class="dash-wrap">
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Main</div>
      <a href="dashboard.php" class="sidebar-link"><span class="icon">🏠</span> Dashboard</a>
      <a href="bookings.php"  class="sidebar-link"><span class="icon">📋</span> Bookings</a>
      <a href="services.php"  class="sidebar-link"><span class="icon">🔧</span> Services</a>
      <a href="reviews.php"   class="sidebar-link active"><span class="icon">⭐</span> Reviews</a>
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
      <h1>Customer Reviews</h1>
      <p>All feedback submitted by customers</p>
    </div>

    <div class="table-card">
      <div class="table-card-header"><span class="table-card-title">Reviews</span></div>
      <table>
        <thead>
          <tr><th>#</th><th>Customer</th><th>Service</th><th>Rating</th><th>Review</th></tr>
        </thead>
        <tbody>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
          <tr>
            <td class="td-muted"><?php echo $row['id']; ?></td>
            <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
            <td><?php echo htmlspecialchars($row['service_name']); ?></td>
            <td><span class="stars"><?php echo str_repeat('★',$row['rating']) . str_repeat('☆',5-$row['rating']); ?></span></td>
            <td class="td-muted"><?php echo htmlspecialchars($row['review']); ?></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<?php include("../includes/footer.php"); ?>
