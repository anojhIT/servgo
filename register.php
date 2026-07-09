<?php
include("config/db.php");
$message  = "";
$msg_type = "error";

if(isset($_POST['register']))
{
    $name     = mysqli_real_escape_string($conn, $_POST['name']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = mysqli_real_escape_string($conn, $_POST['role']);

    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if(mysqli_num_rows($check) > 0) {
        $message = "This email is already registered.";
    } else {
        $sql = "INSERT INTO users(name,email,password,role) VALUES('$name','$email','$password','$role')";
        if(mysqli_query($conn, $sql)) {
            $message  = "Account created! You can now log in.";
            $msg_type = "success";
        } else {
            $message = "Registration failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register — SERVGO</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth-wrap">
  <div class="auth-left">
    <div class="auth-left-logo">SERV<span>GO</span></div>
    <h2>Join<br>SERVGO.</h2>
    <p>Create a free account and get access to hundreds of verified home service professionals in Vavuniya.</p>
    <div class="auth-feature-list">
      <div class="auth-feature"><div class="auth-feature-dot">✓</div> Book as a Customer</div>
      <div class="auth-feature"><div class="auth-feature-dot">✓</div> Earn as a Service Provider</div>
      <div class="auth-feature"><div class="auth-feature-dot">✓</div> Free to join, always</div>
    </div>
  </div>
  <div class="auth-right">
    <div class="auth-right-inner">
      <h3>Create account</h3>
      <p class="auth-sub">Already have one? <a href="login.php">Log in</a></p>
      <?php if($message): ?>
        <div class="alert alert-<?php echo $msg_type; ?>">
          <?php echo ($msg_type=='success') ? '✅' : '⚠️'; ?> <?php echo $message; ?>
        </div>
      <?php endif; ?>
      <form method="POST" class="auth-form">
        <div class="form-group">
          <label class="form-label">Full Name</label>
          <input type="text" name="name" class="form-control" placeholder="Your full name" required>
        </div>
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
        </div>
        <div class="form-group">
          <label class="form-label">Password</label>
          <div style="position:relative;">
            <input type="password" name="password" id="reg-password" class="form-control" placeholder="Min. 6 characters" minlength="6" required>
            <span onclick="togglePwd('reg-password',this)" style="position:absolute;right:1rem;top:50%;transform:translateY(-50%);cursor:pointer;font-size:1rem;color:var(--muted);">👁</span>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">I am joining as</label>
          <select name="role" class="form-control" required>
            <option value="">Select your role…</option>
            <option value="user">👤 Customer — I want to book services</option>
            <option value="provider">🔧 Provider — I offer services</option>
          </select>
        </div>
        <button type="submit" name="register" class="btn btn-primary btn-lg btn-block">Create Account →</button>
      </form>
      <div class="auth-switch">Already have an account? <a href="login.php">Log in</a></div>
    </div>
  </div>
</div>
<script>
function togglePwd(id, el) {
  var input = document.getElementById(id);
  input.type = input.type === 'password' ? 'text' : 'password';
  el.textContent = input.type === 'password' ? '👁' : '🙈';
}
</script>
</body>
</html>
