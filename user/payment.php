<?php
session_start();
include("../config/db.php");

if(!isset($_SESSION['id']) || $_SESSION['role'] != 'user') {
    header("Location: ../login.php"); exit();
}

if(!isset($_GET['service_id'])) {
    header("Location: services.php"); exit();
}

$service_id = $_GET['service_id'];
$sql        = "SELECT services.*, users.name AS provider_name, categories.category_name
               FROM services
               JOIN users      ON services.provider_id = users.id
               JOIN categories ON services.category_id = categories.id
               WHERE services.id = '$service_id'";
$result  = mysqli_query($conn, $sql);
$service = mysqli_fetch_assoc($result);

if(!$service) {
    header("Location: services.php"); exit();
}

include("../includes/header.php");
?>

<div style="min-height:100vh; background:var(--off-white); padding-top:80px;">
<div style="max-width:1000px; margin:0 auto; padding:3rem 2rem; display:grid; grid-template-columns:1fr 400px; gap:2.5rem; align-items:start;">

  <!-- LEFT: Payment Form -->
  <div class="form-card">
    <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1.75rem; padding-bottom:1.25rem; border-bottom:1px solid var(--border);">
      <div style="width:40px;height:40px;border-radius:10px;background:var(--teal-glow);display:flex;align-items:center;justify-content:center;font-size:1.2rem;">🔒</div>
      <div>
        <div style="font-size:1.1rem;font-weight:800;color:var(--text);">Secure Payment</div>
        <div style="font-size:0.78rem;color:var(--muted);">Your payment info is encrypted and safe</div>
      </div>
    </div>

    <!-- Payment Method Selector -->
    <div style="margin-bottom:1.5rem;">
      <label class="form-label">Payment Method</label>
      <div style="display:flex;gap:0.75rem;">
        <div class="pay-method active" onclick="selectMethod(this,'card')" style="flex:1;border:2px solid var(--teal);border-radius:10px;padding:0.85rem;text-align:center;cursor:pointer;transition:all 0.2s;">
          <div style="font-size:1.3rem;">💳</div>
          <div style="font-size:0.8rem;font-weight:600;margin-top:0.3rem;color:var(--teal);">Card</div>
        </div>
        <div class="pay-method" onclick="selectMethod(this,'bank')" style="flex:1;border:2px solid var(--border);border-radius:10px;padding:0.85rem;text-align:center;cursor:pointer;transition:all 0.2s;">
          <div style="font-size:1.3rem;">🏦</div>
          <div style="font-size:0.8rem;font-weight:600;margin-top:0.3rem;color:var(--muted);">Bank Transfer</div>
        </div>
        <div class="pay-method" onclick="selectMethod(this,'cash')" style="flex:1;border:2px solid var(--border);border-radius:10px;padding:0.85rem;text-align:center;cursor:pointer;transition:all 0.2s;">
          <div style="font-size:1.3rem;">💵</div>
          <div style="font-size:0.8rem;font-weight:600;margin-top:0.3rem;color:var(--muted);">Cash</div>
        </div>
      </div>
    </div>

    <!-- CARD FORM -->
    <div id="card-form">

      <!-- Visual Card Preview -->
      <div style="
        background: linear-gradient(135deg, #0F0F1A 0%, #1C1C2E 50%, #00A888 100%);
        border-radius: 16px; padding: 1.75rem; margin-bottom: 1.5rem;
        color: #fff; position: relative; overflow: hidden; min-height: 170px;
      ">
        <div style="position:absolute;top:-30px;right:-30px;width:150px;height:150px;border-radius:50%;background:rgba(255,255,255,0.05);"></div>
        <div style="position:absolute;bottom:-40px;left:60px;width:120px;height:120px;border-radius:50%;background:rgba(0,201,167,0.1);"></div>

        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;position:relative;">
          <div style="font-size:1.1rem;font-weight:900;letter-spacing:-0.04em;">SERV<span style="color:var(--teal);">GO</span></div>
          <div id="card-type-icon" style="font-size:1rem;font-weight:700;color:rgba(255,255,255,0.7);">💳</div>
        </div>

        <div id="preview-number" style="font-size:1.15rem;letter-spacing:0.2em;font-weight:600;margin-bottom:1.25rem;position:relative;">
          •••• •••• •••• ••••
        </div>

        <div style="display:flex;justify-content:space-between;position:relative;">
          <div>
            <div style="font-size:0.62rem;text-transform:uppercase;letter-spacing:0.08em;opacity:0.5;margin-bottom:0.2rem;">Card Holder</div>
            <div id="preview-name" style="font-size:0.85rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;">YOUR NAME</div>
          </div>
          <div>
            <div style="font-size:0.62rem;text-transform:uppercase;letter-spacing:0.08em;opacity:0.5;margin-bottom:0.2rem;">Expires</div>
            <div id="preview-expiry" style="font-size:0.85rem;font-weight:600;">MM/YY</div>
          </div>
        </div>
      </div>

      <!-- Card Form Fields -->
      <form method="POST" action="payment_success.php" id="payment-form">
        <input type="hidden" name="service_id"    value="<?php echo $service_id; ?>">
        <input type="hidden" name="amount"         value="<?php echo $service['price']; ?>">
        <input type="hidden" name="payment_method" id="payment_method_input" value="Card">

        <div class="form-group">
          <label class="form-label">Card Number</label>
          <div style="position:relative;">
            <input type="text" id="card-number" name="card_number" class="form-control"
              placeholder="1234 5678 9012 3456" maxlength="19"
              style="letter-spacing:0.1em; padding-right:3rem;" required>
            <span id="card-brand" style="position:absolute;right:1rem;top:50%;transform:translateY(-50%);font-size:1.2rem;">💳</span>
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
          <div class="form-group">
            <label class="form-label">Expiry Date</label>
            <input type="text" id="card-expiry" name="card_expiry" class="form-control"
              placeholder="MM / YY" maxlength="7" required>
          </div>
          <div class="form-group">
            <label class="form-label">CVV</label>
            <input type="password" id="card-cvv" name="card_cvv" class="form-control"
              placeholder="•••" maxlength="3" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Name on Card</label>
          <input type="text" id="card-name" name="card_name" class="form-control"
            placeholder="e.g. John Silva" required>
        </div>

        <div class="form-group">
          <label class="form-label">📅 Preferred Service Date</label>
          <input type="date" name="booking_date" class="form-control" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
          <small style="color:var(--muted);font-size:0.75rem;">Choose when you want the service done</small>
        </div>

        <button type="submit" id="pay-btn" class="btn btn-cta btn-lg btn-block" style="margin-top:0.5rem;font-size:1rem;">
          🔒 Pay Rs. <?php echo number_format($service['price'], 2); ?>
        </button>

        <div style="text-align:center;margin-top:1rem;font-size:0.78rem;color:var(--muted);">
          🔒 256-bit SSL encrypted · Safe & Secure
        </div>

      </form>
    </div>

    <!-- BANK TRANSFER FORM -->
    <div id="bank-form" style="display:none;">
      <div class="alert alert-info">ℹ️ Transfer to our bank account and confirm below.</div>
      <div style="background:var(--off-white);border-radius:10px;padding:1.25rem;margin-bottom:1.5rem;">
        <div style="font-size:0.78rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--muted);margin-bottom:0.75rem;">Bank Details</div>
        <div style="display:flex;flex-direction:column;gap:0.5rem;font-size:0.875rem;">
          <div><span style="color:var(--muted);">Bank:</span> <strong>Commercial Bank of Ceylon</strong></div>
          <div><span style="color:var(--muted);">Account Name:</span> <strong>SERVGO (Pvt) Ltd</strong></div>
          <div><span style="color:var(--muted);">Account No:</span> <strong>1234 5678 9012</strong></div>
          <div><span style="color:var(--muted);">Branch:</span> <strong>Vavuniya</strong></div>
          <div><span style="color:var(--muted);">Amount:</span> <strong style="color:var(--teal-d);">Rs. <?php echo number_format($service['price'],2); ?></strong></div>
        </div>
      </div>
      <form method="POST" action="payment_success.php">
        <input type="hidden" name="service_id"    value="<?php echo $service_id; ?>">
        <input type="hidden" name="amount"         value="<?php echo $service['price']; ?>">
        <input type="hidden" name="payment_method" value="Bank Transfer">
        <button type="submit" class="btn btn-primary btn-lg btn-block">✅ I Have Transferred — Confirm Booking</button>
      </form>
    </div>

    <!-- CASH FORM -->
    <div id="cash-form" style="display:none;">
      <div class="alert alert-info">💵 Pay in cash when the provider arrives at your location.</div>
      <form method="POST" action="payment_success.php" style="margin-top:1rem;">
        <input type="hidden" name="service_id"    value="<?php echo $service_id; ?>">
        <input type="hidden" name="amount"         value="<?php echo $service['price']; ?>">
        <input type="hidden" name="payment_method" value="Cash on Delivery">
        <button type="submit" class="btn btn-primary btn-lg btn-block">✅ Confirm — Pay Cash on Arrival</button>
      </form>
    </div>

  </div>

  <!-- RIGHT: Order Summary -->
  <div>
    <div class="form-card" style="position:sticky;top:90px;">
      <div style="font-size:1rem;font-weight:800;color:var(--text);margin-bottom:1.25rem;padding-bottom:1rem;border-bottom:1px solid var(--border);">
        📋 Order Summary
      </div>
      <div style="display:flex;flex-direction:column;gap:0.85rem;margin-bottom:1.25rem;">
        <div style="display:flex;justify-content:space-between;font-size:0.875rem;">
          <span style="color:var(--muted);">Service</span>
          <span style="font-weight:600;"><?php echo htmlspecialchars($service['service_name']); ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:0.875rem;">
          <span style="color:var(--muted);">Category</span>
          <span><?php echo htmlspecialchars($service['category_name']); ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:0.875rem;">
          <span style="color:var(--muted);">Provider</span>
          <span><?php echo htmlspecialchars($service['provider_name']); ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:0.875rem;">
          <span style="color:var(--muted);">Customer</span>
          <span><?php echo htmlspecialchars($_SESSION['name']); ?></span>
        </div>
      </div>
      <div style="border-top:1px solid var(--border);padding-top:1rem;margin-bottom:1rem;">
        <div style="display:flex;justify-content:space-between;font-size:0.875rem;margin-bottom:0.5rem;">
          <span style="color:var(--muted);">Subtotal</span>
          <span>Rs. <?php echo number_format($service['price'],2); ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:0.875rem;">
          <span style="color:var(--muted);">Service Fee</span>
          <span style="color:var(--teal-d);">Free</span>
        </div>
      </div>
      <div style="background:var(--off-white);border-radius:10px;padding:1rem;display:flex;justify-content:space-between;align-items:center;">
        <span style="font-weight:700;">Total</span>
        <span style="font-size:1.4rem;font-weight:900;color:var(--teal-d);">Rs. <?php echo number_format($service['price'],2); ?></span>
      </div>
      <div style="margin-top:1.25rem;display:flex;flex-direction:column;gap:0.5rem;">
        <div style="font-size:0.78rem;color:var(--muted);">✅ Verified service provider</div>
        <div style="font-size:0.78rem;color:var(--muted);">✅ Booking confirmation sent</div>
        <div style="font-size:0.78rem;color:var(--muted);">✅ Cancel anytime before service</div>
      </div>
    </div>
    <div style="text-align:center;margin-top:1rem;">
      <a href="services.php" class="back-link">← Back to Services</a>
    </div>
  </div>

</div>
</div>

<script>

/* ── 1. Payment method selector ── */
function selectMethod(el, method) {
  document.querySelectorAll('.pay-method').forEach(function(m) {
    m.style.border = '2px solid var(--border)';
    m.querySelector('div:last-child').style.color = 'var(--muted)';
  });
  el.style.border = '2px solid var(--teal)';
  el.querySelector('div:last-child').style.color = 'var(--teal)';

  document.getElementById('card-form').style.display = (method === 'card') ? 'block' : 'none';
  document.getElementById('bank-form').style.display = (method === 'bank') ? 'block' : 'none';
  document.getElementById('cash-form').style.display = (method === 'cash') ? 'block' : 'none';

  var input = document.getElementById('payment_method_input');
  if(input) {
    if(method === 'card') input.value = 'Card';
    else if(method === 'bank') input.value = 'Bank Transfer';
    else input.value = 'Cash on Delivery';
  }
}

/* ── 2. Live card number format ── */
document.getElementById('card-number').addEventListener('input', function() {
  var val = this.value.replace(/\D/g, '').substring(0, 16);
  var groups = val.match(/.{1,4}/g);
  this.value = groups ? groups.join(' ') : val;

  var display = val.padEnd(16, '•').match(/.{1,4}/g).join(' ');
  document.getElementById('preview-number').textContent = display;

  if(val.startsWith('4'))      { document.getElementById('card-type-icon').textContent = 'VISA'; }
  else if(val.startsWith('5')) { document.getElementById('card-type-icon').textContent = 'MC'; }
  else                          { document.getElementById('card-type-icon').textContent = '💳'; }
});

/* ── 3. Live expiry format ── */
document.getElementById('card-expiry').addEventListener('input', function() {
  var val = this.value.replace(/\D/g, '').substring(0, 4);
  if(val.length >= 2) {
    val = val.substring(0, 2) + ' / ' + val.substring(2);
  }
  this.value = val;
  document.getElementById('preview-expiry').textContent = val || 'MM/YY';
});

/* ── 4. Live name update ── */
document.getElementById('card-name').addEventListener('input', function() {
  var val = this.value.toUpperCase();
  document.getElementById('preview-name').textContent = val || 'YOUR NAME';
});

/* ── 5. Validate and submit ── */
document.getElementById('payment-form').addEventListener('submit', function(e) {
  var num  = document.getElementById('card-number').value.replace(/\s+/g, '');
  var exp  = document.getElementById('card-expiry').value.replace(/\s/g, '');
  var cvv  = document.getElementById('card-cvv').value;
  var name = document.getElementById('card-name').value;

  if(num.length < 16) {
    alert('Please enter a valid 16-digit card number.');
    e.preventDefault(); return;
  }
  if(exp.length < 4) {
    alert('Please enter a valid expiry date. Example: 12 / 26');
    e.preventDefault(); return;
  }
  if(cvv.length < 3) {
    alert('Please enter a valid 3-digit CVV.');
    e.preventDefault(); return;
  }
  if(!name.trim()) {
    alert('Please enter the name on card.');
    e.preventDefault(); return;
  }

  var btn = document.getElementById('pay-btn');
  btn.disabled     = true;
  btn.innerHTML    = '⏳ Processing payment...';
  btn.style.opacity = '0.8';
});

</script>

<?php include("../includes/footer.php"); ?>
