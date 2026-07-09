<?php
session_start();
include("../config/db.php");
if(!isset($_SESSION['id']) || $_SESSION['role'] != 'provider') { header("Location: ../login.php"); exit(); }

$pid = $_SESSION['id'];

/* Delete service */
if(isset($_GET['delete'])) {
    $sid = mysqli_real_escape_string($conn, $_GET['delete']);
    mysqli_query($conn, "DELETE FROM services WHERE id='$sid' AND provider_id='$pid'");
    header("Location: my_services.php?msg=Service+deleted+successfully!");
    exit();
}

$result = mysqli_query($conn,
    "SELECT services.*, categories.category_name
     FROM services
     JOIN categories ON services.category_id=categories.id
     WHERE services.provider_id='$pid'
     ORDER BY services.id DESC"
);
$total = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM services WHERE provider_id='$pid'"))[0];

include("../includes/header.php");
?>

<!-- Delete Modal -->
<div id="delete-modal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(10,10,20,0.6);backdrop-filter:blur(6px);align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:20px;padding:2.5rem 2rem;max-width:380px;width:90%;text-align:center;box-shadow:0 32px 80px rgba(0,0,0,0.35);animation:popIn 0.25s cubic-bezier(.22,1,.36,1);">
    <div style="font-size:2.5rem;margin-bottom:1rem;">🗑️</div>
    <div style="font-size:1.15rem;font-weight:800;color:var(--text);margin-bottom:0.5rem;">Delete Service</div>
    <div style="font-size:0.875rem;color:var(--muted);margin-bottom:1.75rem;line-height:1.6;">Are you sure? This service and all its bookings history will be removed permanently.</div>
    <div style="display:flex;gap:0.75rem;">
      <button onclick="document.getElementById('delete-modal').style.display='none'" style="flex:1;padding:0.75rem;border-radius:10px;border:1.5px solid var(--border);background:#fff;font-family:inherit;font-size:0.875rem;font-weight:600;color:var(--muted);cursor:pointer;">Keep It</button>
      <a id="delete-confirm-btn" href="#" style="flex:1;padding:0.75rem;border-radius:10px;background:#EF4444;color:#fff;font-family:inherit;font-size:0.875rem;font-weight:700;cursor:pointer;text-decoration:none;display:flex;align-items:center;justify-content:center;">Yes, Delete</a>
    </div>
  </div>
</div>
<style>@keyframes popIn{0%{transform:scale(0.85);opacity:0}100%{transform:scale(1);opacity:1}}</style>

<div class="dash-wrap">
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Menu</div>
      <a href="dashboard.php"   class="sidebar-link"><span class="icon">🏠</span> Dashboard</a>
      <a href="add_service.php" class="sidebar-link"><span class="icon">➕</span> Add Service</a>
      <a href="my_services.php" class="sidebar-link active"><span class="icon">🔧</span> My Services</a>
      <a href="bookings.php"    class="sidebar-link"><span class="icon">📋</span> My Bookings</a>
      <a href="earnings.php"    class="sidebar-link"><span class="icon">💰</span> Earnings</a>
      <a href="profile.php"     class="sidebar-link"><span class="icon">👤</span> My Profile</a>
    </div>
    <a href="../logout.php" class="sidebar-link" style="margin-top:1rem;"><span class="icon">🚪</span> Sign Out</a>
  </aside>

  <main class="dash-main">
    <?php if(isset($_GET['msg'])): ?>
      <div class="alert alert-success">✅ <?php echo htmlspecialchars($_GET['msg']); ?></div>
    <?php endif; ?>

    <div class="dash-header" style="display:flex;justify-content:space-between;align-items:flex-start;">
      <div>
        <h1>My Services</h1>
        <p>All services you have listed — <?php echo $total; ?> total</p>
      </div>
      <a href="add_service.php" class="btn btn-primary">➕ Add New Service</a>
    </div>

    <?php if($total == 0): ?>
      <div class="empty-state" style="margin-top:3rem;">
        <div class="empty-icon">🔧</div>
        <p>You haven't listed any services yet.</p>
        <a href="add_service.php" class="btn btn-primary" style="margin-top:1rem;">➕ Add Your First Service</a>
      </div>
    <?php else: ?>

    <div class="browse-grid">
    <?php while($row = mysqli_fetch_assoc($result)): ?>
      <div class="browse-card">
        <span class="browse-cat"><?php echo htmlspecialchars($row['category_name']); ?></span>
        <div class="browse-name" style="margin-top:0.5rem;"><?php echo htmlspecialchars($row['service_name']); ?></div>
        <?php if(!empty($row['description'])): ?>
          <p style="font-size:0.82rem;color:var(--muted);margin:0.5rem 0;line-height:1.6;"><?php echo htmlspecialchars($row['description']); ?></p>
        <?php endif; ?>
        <div class="browse-price">Rs. <?php echo number_format($row['price'],2); ?> <span>/ service</span></div>
        <div style="display:flex;gap:0.6rem;margin-top:0.75rem;">
          <a href="add_service.php?edit=<?php echo $row['id']; ?>" class="btn btn-outline btn-sm" style="flex:1;justify-content:center;">✏️ Edit</a>
          <button onclick="document.getElementById('delete-confirm-btn').href='my_services.php?delete=<?php echo $row['id']; ?>';document.getElementById('delete-modal').style.display='flex';"
            class="btn btn-danger btn-sm" style="flex:1;justify-content:center;">🗑️ Delete</button>
        </div>
      </div>
    <?php endwhile; ?>
    </div>

    <?php endif; ?>
  </main>
</div>

<?php include("../includes/footer.php"); ?>
