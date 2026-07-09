<?php
session_start();
include("../config/db.php");
if(!isset($_SESSION['id']) || $_SESSION['role'] != 'user') { header("Location: ../login.php"); exit(); }

$message  = "";
$msg_type = "success";

if(isset($_POST['submit_review']))
{
    $booking_id = $_POST['booking_id'];
    $user_id    = $_SESSION['id'];
    $rating     = $_POST['rating'];
    $review     = $_POST['review'];

    $booking = mysqli_query($conn,"SELECT * FROM bookings WHERE id='$booking_id'");
    if(mysqli_num_rows($booking) > 0) {
        $data       = mysqli_fetch_assoc($booking);
        $service_id = $data['service_id'];
        $sql = "INSERT INTO reviews(booking_id,user_id,service_id,rating,review) VALUES('$booking_id','$user_id','$service_id','$rating','$review')";
        if(mysqli_query($conn,$sql)) {
            $message = "Your review has been submitted. Thank you!";
        } else {
            $message  = mysqli_error($conn);
            $msg_type = "error";
        }
    } else {
        $message  = "Invalid Booking ID.";
        $msg_type = "error";
    }
}

/* Load this user's accepted bookings for reference */
$uid = $_SESSION['id'];
$bookings_res = mysqli_query($conn,"SELECT bookings.id, services.service_name FROM bookings JOIN services ON bookings.service_id=services.id WHERE bookings.user_id='$uid' AND bookings.status='Accepted'");
include("../includes/header.php");
?>

<div class="dash-wrap">
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Menu</div>
      <a href="dashboard.php"   class="sidebar-link"><span class="icon">🏠</span> Dashboard</a>
      <a href="services.php"    class="sidebar-link"><span class="icon">🔧</span> Browse Services</a>
      <a href="my_bookings.php" class="sidebar-link"><span class="icon">📋</span> My Bookings</a>
      <a href="reviews.php"     class="sidebar-link active"><span class="icon">⭐</span> Write Review</a>
      <a href="profile.php"     class="sidebar-link"><span class="icon">👤</span> My Profile</a>
    </div>
    <a href="../logout.php" class="sidebar-link" style="margin-top:1rem;"><span class="icon">🚪</span> Sign Out</a>
  </aside>

  <main class="dash-main">
    <div class="dash-header">
      <h1>Write a Review</h1>
      <p>Share your experience to help others choose the right professional</p>
    </div>

    <?php if($message): ?>
      <div class="alert alert-<?php echo $msg_type; ?>">
        <?php echo ($msg_type=='success') ? '✅' : '⚠️'; ?> <?php echo $message; ?>
      </div>
    <?php endif; ?>

    <div class="form-card" style="max-width:560px;">
      <div class="form-card-title">⭐ Submit Your Review</div>
      <form method="POST">

        <div class="form-group">
          <label class="form-label">Select Booking</label>
          <select name="booking_id" class="form-control" required>
            <option value="">Choose a completed booking…</option>
            <?php while($b = mysqli_fetch_assoc($bookings_res)): ?>
              <option value="<?php echo $b['id']; ?>">
                #<?php echo $b['id']; ?> — <?php echo htmlspecialchars($b['service_name']); ?>
              </option>
            <?php endwhile; ?>
          </select>
          <small style="color:var(--muted);font-size:.78rem;">Or enter Booking ID manually below</small>
        </div>

        <div class="form-group">
          <label class="form-label">Booking ID</label>
          <input type="number" name="booking_id" class="form-control" placeholder="e.g. 12" required>
        </div>

        <div class="form-group">
          <label class="form-label">Rating</label>
          <select name="rating" class="form-control" required>
            <option value="">Select rating…</option>
            <option value="5">⭐⭐⭐⭐⭐ — Excellent</option>
            <option value="4">⭐⭐⭐⭐ — Good</option>
            <option value="3">⭐⭐⭐ — Average</option>
            <option value="2">⭐⭐ — Poor</option>
            <option value="1">⭐ — Very Poor</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Your Review</label>
          <textarea name="review" class="form-control" placeholder="Describe your experience…" required></textarea>
        </div>

        <button type="submit" name="submit_review" class="btn btn-primary btn-lg btn-block">Submit Review →</button>
      </form>
    </div>
  </main>
</div>

<?php include("../includes/footer.php"); ?>
