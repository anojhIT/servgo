<?php
session_start();
include("config/db.php");
$message = "";

if(isset($_POST['login']))
{
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql    = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        /* Support both hashed and old plain text passwords */
        $valid = password_verify($password, $user['password']);
        if(!$valid && $password === $user['password']) $valid = true;

        if($valid) {
            $_SESSION['id']   = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            if($user['role'] == 'admin')    { header("Location: admin/dashboard.php");    exit(); }
            if($user['role'] == 'user')     { header("Location: user/dashboard.php");     exit(); }
            if($user['role'] == 'provider') { header("Location: provider/dashboard.php"); exit(); }
        } else {
            $message = "Invalid email or password.";
        }
    } else {
        $message = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Log In — SERVGO</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth-wrap">
  <div class="auth-left">
    <div class="auth-left-logo">SERV<span>GO</span></div>
    <h2>Welcome<br>back.</h2>
    <p>Log in to book services, track your appointments, and connect with trusted professionals.</p>
    <div class="auth-feature-list">
      <div class="auth-feature"><div class="auth-feature-dot">✓</div> Verified service providers</div>
      <div class="auth-feature"><div class="auth-feature-dot">✓</div> Real-time booking tracking</div>
      <div class="auth-feature"><div class="auth-feature-dot">✓</div> Secure payments</div>
    </div>
  </div>
  <div class="auth-right">
    <div class="auth-right-inner">
      <h3>Log in</h3>
      <p class="auth-sub">Don't have an account? <a href="register.php">Sign up free</a></p>
      <?php if($message): ?>
        <div class="alert alert-error">⚠️ <?php echo $message; ?></div>
      <?php endif; ?>
      <form method="POST" class="auth-form">
        <div class="form-group">
          <label class="form-label">Email address</label>
          <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
        </div>
        <div class="form-group">
          <label class="form-label">Password</label>
          <div style="position:relative;">
            <input type="password" name="password" id="login-password" class="form-control" placeholder="••••••••" required>
            <span onclick="togglePwd('login-password',this)" style="position:absolute;right:1rem;top:50%;transform:translateY(-50%);cursor:pointer;font-size:1rem;color:var(--muted);">👁</span>
          </div>
        </div>
        <button type="submit" name="login" class="btn btn-primary btn-lg btn-block">Log In →</button>
      </form>
      <div class="auth-switch">New to SERVGO? <a href="register.php">Create an account</a></div>
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
