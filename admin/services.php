<?php
session_start();
include("../config/db.php");
if(!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') { header("Location: ../login.php"); exit(); }

$sql = "SELECT services.id, services.service_name, services.price, services.status,
               users.name AS provider_name, categories.category_name
        FROM services
        JOIN users       ON services.provider_id  = users.id
        JOIN categories  ON services.category_id  = categories.id
        ORDER BY services.id DESC";
$result = mysqli_query($conn,$sql);
include("../includes/header.php");
?>

<div class="dash-wrap">
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Main</div>
      <a href="dashboard.php" class="sidebar-link"><span class="icon">🏠</span> Dashboard</a>
      <a href="bookings.php"  class="sidebar-link"><span class="icon">📋</span> Bookings</a>
      <a href="services.php"  class="sidebar-link active"><span class="icon">🔧</span> Services</a>
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
      <h1>All Services</h1>
      <p>Every service listed by providers</p>
    </div>

    <div class="table-card">
      <div class="table-card-header"><span class="table-card-title">Services</span></div>
      <table>
        <thead>
          <tr><th>#</th><th>Service Name</th><th>Category</th><th>Provider</th><th>Price</th><th>Status</th></tr>
        </thead>
        <tbody>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
          <tr>
            <td class="td-muted"><?php echo $row['id']; ?></td>
            <td><strong><?php echo htmlspecialchars($row['service_name']); ?></strong></td>
            <td class="td-muted"><?php echo htmlspecialchars($row['category_name']); ?></td>
            <td><?php echo htmlspecialchars($row['provider_name']); ?></td>
            <td><strong>Rs. <?php echo number_format($row['price'],2); ?></strong></td>
            <td>
              <?php $s = strtolower($row['status'] ?? 'active');
                    $cls = ($s=='active') ? 'badge-accepted' : 'badge-pending'; ?>
              <span class="badge <?php echo $cls; ?>"><?php echo ucfirst($row['status'] ?? 'Active'); ?></span>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<?php include("../includes/footer.php"); ?>
