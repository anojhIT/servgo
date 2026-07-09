<?php
session_start();
include("../config/db.php");
if(!isset($_SESSION['id']) || $_SESSION['role'] != 'user') { header("Location: ../login.php"); exit(); }

$message  = "";
$msg_type = "success";

if(isset($_GET['book']))
{
    $service_id = $_GET['book'];
    $user_id    = $_SESSION['id'];
    $sql = "INSERT INTO bookings(user_id,service_id,booking_date,status) VALUES('$user_id','$service_id',NOW(),'Pending')";
    if(mysqli_query($conn,$sql)) {
        $message = "Booking submitted! Waiting for approval.";
    } else {
        $message  = "Booking failed. Please try again.";
        $msg_type = "error";
    }
}

$category  = isset($_GET['category']) ? $_GET['category'] : "";
$cat_query = mysqli_query($conn,"SELECT * FROM categories");

if($category != "") {
    $sql = "SELECT services.*, users.name AS provider_name, categories.category_name
            FROM services JOIN users ON services.provider_id=users.id
            JOIN categories ON services.category_id=categories.id
            WHERE services.category_id='$category'";
} else {
    $sql = "SELECT services.*, users.name AS provider_name, categories.category_name
            FROM services JOIN users ON services.provider_id=users.id
            JOIN categories ON services.category_id=categories.id";
}
$result = mysqli_query($conn,$sql);
include("../includes/header.php");
?>

<div class="dash-wrap">
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Menu</div>
      <a href="dashboard.php"   class="sidebar-link"><span class="icon">🏠</span> Dashboard</a>
      <a href="services.php"    class="sidebar-link active"><span class="icon">🔧</span> Browse Services</a>
      <a href="my_bookings.php" class="sidebar-link"><span class="icon">📋</span> My Bookings</a>
      <a href="reviews.php"     class="sidebar-link"><span class="icon">⭐</span> Write Review</a>
      <a href="profile.php"     class="sidebar-link"><span class="icon">👤</span> My Profile</a>
    </div>
    <a href="../logout.php" class="sidebar-link" style="margin-top:1rem;"><span class="icon">🚪</span> Sign Out</a>
  </aside>

  <main class="dash-main">
    <div class="dash-header">
      <h1>Browse Services</h1>
      <p>Find the right professional for your home</p>
    </div>

    <?php if($message): ?>
      <div class="alert alert-<?php echo $msg_type; ?>">
        <?php echo ($msg_type=='success') ? '✅' : '⚠️'; ?> <?php echo $message; ?>
      </div>
    <?php endif; ?>

    <!-- Live Search -->
    <div style="margin-bottom:1rem;">
      <input type="text" id="service-search" class="form-control" placeholder="🔍 Search services by name..." style="max-width:400px;">
    </div>

    <!-- Filter bar -->
    <form method="GET" class="filter-bar">
      <div class="form-group">
        <label class="form-label">Filter by Category</label>
        <select name="category" class="form-control">
          <option value="">All Categories</option>
          <?php while($cat=mysqli_fetch_assoc($cat_query)): ?>
            <option value="<?php echo $cat['id']; ?>" <?php echo ($category==$cat['id'])? 'selected':''; ?>>
              <?php echo htmlspecialchars($cat['category_name']); ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Search</button>
      <?php if($category): ?>
        <a href="services.php" class="btn btn-outline">Clear</a>
      <?php endif; ?>
    </form>

    <!-- Service cards -->
    <div class="browse-grid">
    <?php
    $has = false;
    while($row = mysqli_fetch_assoc($result)):
      $has = true;
    ?>
      <div class="browse-card">
        <div class="browse-provider">🔨 <?php echo htmlspecialchars($row['provider_name']); ?></div>
        <div class="browse-name"><?php echo htmlspecialchars($row['service_name']); ?></div>
        <span class="browse-cat"><?php echo htmlspecialchars($row['category_name']); ?></span>
        <?php if(!empty($row['description'])): ?>
          <p style="font-size:.82rem;color:var(--muted);margin-bottom:.75rem;line-height:1.6;"><?php echo htmlspecialchars($row['description']); ?></p>
        <?php endif; ?>
        <div class="browse-price">Rs. <?php echo number_format($row['price'],2); ?> <span>/ service</span></div>
        <a href="payment.php?service_id=<?php echo $row['id']; ?>" class="btn btn-cta btn-block">
          💳 Book & Pay →
        </a>
      </div>
    <?php endwhile; ?>
    <?php if(!$has): ?>
      <div class="empty-state" style="grid-column:1/-1;">
        <div class="empty-icon">🔍</div>
        <p>No services found for this category.</p>
      </div>
    <?php endif; ?>
    </div>
  </main>
</div>

<script>
document.getElementById("service-search").addEventListener("input", function() {
  var filter = this.value.toLowerCase();
  document.querySelectorAll(".browse-card").forEach(function(card) {
    var text = card.innerText.toLowerCase();
    card.style.display = text.includes(filter) ? "" : "none";
  });
  var visible = document.querySelectorAll(".browse-card:not([style*="none"])");
  var empty = document.getElementById("search-empty");
  if(filter && visible.length === 0) {
    if(!empty) {
      var d = document.createElement("div");
      d.id = "search-empty";
      d.className = "empty-state";
      d.style.gridColumn = "1/-1";
      d.innerHTML = '<div class="empty-icon">🔍</div><p>No services found for "<strong>' + filter + '</strong>".</p>';
      document.querySelector(".browse-grid").appendChild(d);
    }
  } else if(empty) { empty.remove(); }
});
</script>

<?php include("../includes/footer.php"); ?>
