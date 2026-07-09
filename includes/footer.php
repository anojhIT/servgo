<footer class="footer">
  <div class="footer-inner">
    <div class="footer-logo">SERV<span>GO</span></div>
    <div class="footer-links">
      <a href="<?php echo $root ?? ''; ?>index.php">Home</a>
      <a href="<?php echo $root ?? ''; ?>about.php">About</a>
      <a href="<?php echo $root ?? ''; ?>contact.php">Contact</a>
    </div>
  </div>
  <div class="footer-copy">© <?php echo date('Y'); ?> SERVGO · Omanthai, Vavuniya · All rights reserved.</div>
</footer>

<!-- Dark Mode Toggle -->
<button class="dark-toggle" onclick="toggleDark()" id="dark-btn" title="Toggle dark mode">🌙</button>

<script src="<?php echo $root ?? ''; ?>assets/js/main.js"></script>
<script>
/* Dark mode */
function toggleDark() {
  var body = document.body;
  body.classList.toggle('dark-mode');
  var isDark = body.classList.contains('dark-mode');
  localStorage.setItem('servgo-dark', isDark ? '1' : '0');
  document.getElementById('dark-btn').textContent = isDark ? '☀️' : '🌙';
}
/* Apply saved preference */
if(localStorage.getItem('servgo-dark') === '1') {
  document.body.classList.add('dark-mode');
  var btn = document.getElementById('dark-btn');
  if(btn) btn.textContent = '☀️';
}
</script>

</body>
</html>
