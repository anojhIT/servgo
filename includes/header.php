<?php
session_start();

/* Calculate root path based on current file location */
$current = $_SERVER['PHP_SELF'];

if(strpos($current, '/admin/')    !== false ||
   strpos($current, '/user/')     !== false ||
   strpos($current, '/provider/') !== false) {
    $root = '../';
} else {
    $root = '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SERVGO — Home Services Marketplace</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="<?php echo $root; ?>assets/css/style.css">
</head>
<body>

<nav class="nav">
  <a href="<?php echo $root; ?>index.php" class="nav-logo">SERV<span>GO</span></a>

  <ul class="nav-links">
    <li><a href="<?php echo $root; ?>index.php">Home</a></li>
    <li><a href="<?php echo $root; ?>about.php">About</a></li>
    <li><a href="<?php echo $root; ?>contact.php">Contact</a></li>
    <?php if(isset($_SESSION['role'])): ?>
      <li><a href="<?php echo $root . $_SESSION['role']; ?>/dashboard.php">Dashboard</a></li>
    <?php endif; ?>
  </ul>

  <div class="nav-actions">
    <?php if(isset($_SESSION['name'])): ?>
      <span class="nav-user">Hi, <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong></span>
      <a href="<?php echo $root; ?>logout.php" class="btn btn-outline-light btn-sm">Sign Out</a>
    <?php else: ?>
      <a href="<?php echo $root; ?>login.php"    class="btn btn-outline-light btn-sm">Log In</a>
      <a href="<?php echo $root; ?>register.php" class="btn btn-primary btn-sm">Get Started</a>
    <?php endif; ?>
  </div>
</nav>