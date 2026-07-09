<?php
session_start();
include("../config/db.php");
if(!isset($_SESSION['id']) || $_SESSION['role'] != 'provider') { header("Location: ../login.php"); exit(); }

$message  = "";
$msg_type = "success";

if(isset($_POST['add_service']))
{
    $provider_id  = $_SESSION['id'];
    $category_id  = $_POST['category_id'];
    $service_name = $_POST['service_name'];
    $description  = $_POST['description'];
    $price        = $_POST['price'];

    $sql = "INSERT INTO services(provider_id,category_id,service_name,description,price) VALUES('$provider_id','$category_id','$service_name','$description','$price')";
    if(mysqli_query($conn,$sql)) {
        $message = "Service added successfully!";
    } else {
        $message  = "Failed to add service. Please try again.";
        $msg_type = "error";
    }
}

$categories = mysqli_query($conn,"SELECT * FROM categories");
include("../includes/header.php");
?>

<div class="dash-wrap">
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Menu</div>
      <a href="dashboard.php"   class="sidebar-link"><span class="icon">🏠</span> Dashboard</a>
      <a href="add_service.php" class="sidebar-link active"><span class="icon">➕</span> Add Service</a>
      <a href="bookings.php"    class="sidebar-link"><span class="icon">📋</span> My Bookings</a>
      <a href="profile.php"     class="sidebar-link"><span class="icon">👤</span> My Profile</a>
    </div>
    <a href="../logout.php" class="sidebar-link" style="margin-top:1rem;"><span class="icon">🚪</span> Sign Out</a>
  </aside>

  <main class="dash-main">
    <div class="dash-header">
      <h1>Add a Service</h1>
      <p>List a new service that customers can book</p>
    </div>

    <?php if($message): ?>
      <div class="alert alert-<?php echo $msg_type; ?>">
        <?php echo ($msg_type=='success') ? '✅' : '⚠️'; ?> <?php echo $message; ?>
      </div>
    <?php endif; ?>

    <div class="form-card" style="max-width:560px;">
      <div class="form-card-title">🔧 New Service Details</div>
      <form method="POST">

        <div class="form-group">
          <label class="form-label">Category</label>
          <select name="category_id" class="form-control" required>
            <option value="">Select a category…</option>
            <?php while($row = mysqli_fetch_assoc($categories)): ?>
              <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['category_name']); ?></option>
            <?php endwhile; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Service Name</label>
          <input type="text" name="service_name" class="form-control" placeholder="e.g. Pipe Leak Repair" required>
        </div>

        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" placeholder="Describe what you offer…"></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">Price (Rs.)</label>
          <input type="number" step="0.01" name="price" class="form-control" placeholder="e.g. 2500" required>
        </div>

        <button type="submit" name="add_service" class="btn btn-primary btn-lg btn-block">Add Service →</button>
      </form>
    </div>
  </main>
</div>

<?php include("../includes/footer.php"); ?>
