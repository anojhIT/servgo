<?php
session_start();
include("../config/db.php");
if(!isset($_SESSION['id']) || $_SESSION['role'] != 'provider') { header("Location: ../login.php"); exit(); }

$provider_id = $_SESSION['id'];
$message     = "";

if(isset($_POST['update']))
{
    $name  = $_POST['name'];
    $email = $_POST['email'];
    mysqli_query($conn,"UPDATE users SET name='$name',email='$email' WHERE id='$provider_id'");
    $_SESSION['name'] = $name;
    $message = "Profile updated successfully!";
}

$result = mysqli_query($conn,"SELECT * FROM users WHERE id='$provider_id'");
$user   = mysqli_fetch_assoc($result);
include("../includes/header.php");
?>

<div class="dash-wrap">
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Menu</div>
      <a href="dashboard.php"   class="sidebar-link"><span class="icon">🏠</span> Dashboard</a>
      <a href="add_service.php" class="sidebar-link"><span class="icon">➕</span> Add Service</a>
      <a href="bookings.php"    class="sidebar-link"><span class="icon">📋</span> My Bookings</a>
      <a href="profile.php"     class="sidebar-link active"><span class="icon">👤</span> My Profile</a>
    </div>
    <a href="../logout.php" class="sidebar-link" style="margin-top:1rem;"><span class="icon">🚪</span> Sign Out</a>
  </aside>

  <main class="dash-main">
    <div class="dash-header">
      <h1>My Profile</h1>
      <p>Manage your provider account</p>
    </div>

    <?php if($message): ?>
      <div class="alert alert-success">✅ <?php echo $message; ?></div>
    <?php endif; ?>

    <div class="form-card" style="max-width:520px;">
      <div class="form-card-title">👤 Account Details</div>
      <form method="POST">
        <div class="form-group">
          <label class="form-label">Full Name</label>
          <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Role</label>
          <input type="text" class="form-control" value="Service Provider" disabled style="background:#f3f4f6;color:var(--muted);">
        </div>
        <button type="submit" name="update" class="btn btn-primary btn-lg">Save Changes</button>
      </form>
    </div>
  </main>
</div>

<?php include("../includes/footer.php"); ?>
