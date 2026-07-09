<?php
session_start();
include("../config/db.php");
if(!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') { header("Location: ../login.php"); exit(); }

/* Delete user */
if(isset($_GET['delete'])) {
    $uid = mysqli_real_escape_string($conn, $_GET['delete']);
    if($uid != $_SESSION['id']) {
        mysqli_query($conn, "DELETE FROM users WHERE id='$uid'");
        header("Location: users.php?msg=User+deleted+successfully!");
        exit();
    }
}

$result = mysqli_query($conn,"SELECT * FROM users ORDER BY id DESC");
include("../includes/header.php");
?>

<!-- Delete Modal -->
<div id="delete-modal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(10,10,20,0.6);backdrop-filter:blur(6px);align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:20px;padding:2.5rem 2rem;max-width:380px;width:90%;text-align:center;box-shadow:0 32px 80px rgba(0,0,0,0.35);animation:popIn 0.25s cubic-bezier(.22,1,.36,1);">
    <div style="font-size:2.5rem;margin-bottom:1rem;">🗑️</div>
    <div style="font-size:1.15rem;font-weight:800;color:var(--text);margin-bottom:0.5rem;">Delete User</div>
    <div style="font-size:0.875rem;color:var(--muted);margin-bottom:1.75rem;line-height:1.6;">Are you sure? This user account will be permanently removed.</div>
    <div style="display:flex;gap:0.75rem;">
      <button onclick="document.getElementById('delete-modal').style.display='none'" style="flex:1;padding:0.75rem;border-radius:10px;border:1.5px solid var(--border);background:#fff;font-family:inherit;font-size:0.875rem;font-weight:600;color:var(--muted);cursor:pointer;">Cancel</button>
      <a id="delete-btn" href="#" style="flex:1;padding:0.75rem;border-radius:10px;background:#EF4444;color:#fff;font-family:inherit;font-size:0.875rem;font-weight:700;text-decoration:none;display:flex;align-items:center;justify-content:center;">Yes, Delete</a>
    </div>
  </div>
</div>
<style>@keyframes popIn{0%{transform:scale(0.85);opacity:0}100%{transform:scale(1);opacity:1}}</style>

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
      <a href="users.php"     class="sidebar-link active"><span class="icon">👤</span> Users</a>
      <a href="providers.php" class="sidebar-link"><span class="icon">🔨</span> Providers</a>
    </div>
    <a href="../logout.php" class="sidebar-link" style="margin-top:1rem;"><span class="icon">🚪</span> Sign Out</a>
  </aside>

  <main class="dash-main">
    <?php if(isset($_GET['msg'])): ?>
      <div class="alert alert-success">✅ <?php echo htmlspecialchars($_GET['msg']); ?></div>
    <?php endif; ?>

    <div class="dash-header">
      <h1>All Users</h1>
      <p>Every registered account on the platform</p>
    </div>

    <div class="table-card">
      <div class="table-card-header"><span class="table-card-title">Registered Users</span></div>
      <table>
        <thead>
          <tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Action</th></tr>
        </thead>
        <tbody>
        <?php while($row = mysqli_fetch_assoc($result)):
          $roleMap=['admin'=>'badge-admin','user'=>'badge-user','provider'=>'badge-provider'];
          $cls=$roleMap[$row['role']]??'badge-user';
        ?>
          <tr>
            <td class="td-muted"><?php echo $row['id']; ?></td>
            <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
            <td class="td-muted"><?php echo htmlspecialchars($row['email']); ?></td>
            <td><span class="badge <?php echo $cls; ?>"><?php echo ucfirst($row['role']); ?></span></td>
            <td>
              <?php if($row['id'] != $_SESSION['id'] && $row['role'] != 'admin'): ?>
                <button onclick="document.getElementById('delete-btn').href='users.php?delete=<?php echo $row['id']; ?>';document.getElementById('delete-modal').style.display='flex';"
                  class="btn btn-danger btn-sm">🗑️ Delete</button>
              <?php else: ?>
                <span class="td-muted">—</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<script>
document.getElementById('delete-modal').addEventListener('click',function(e){if(e.target===this)this.style.display='none';});
document.addEventListener('keydown',function(e){if(e.key==='Escape')document.getElementById('delete-modal').style.display='none';});
</script>

<?php include("../includes/footer.php"); ?>
