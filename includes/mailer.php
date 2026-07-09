<?php
/* ================================================
   SERVGO Simple Mailer
   Uses PHP mail() function
   ================================================ */

function sendEmail($to_email, $to_name, $subject, $body) {
    $from    = "noreply@servgo.com";
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: SERVGO <$from>\r\n";
    $headers .= "Reply-To: info@servgo.com\r\n";

    $html = '
    <!DOCTYPE html>
    <html>
    <head>
      <style>
        body { font-family: Inter, Arial, sans-serif; background:#F7F8FA; margin:0; padding:0; }
        .wrap { max-width:560px; margin:2rem auto; background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,0.08); }
        .header { background:linear-gradient(135deg,#0A0A14,#1C1C2E); padding:2rem; text-align:center; }
        .logo { font-size:1.5rem; font-weight:900; color:#fff; letter-spacing:-0.04em; }
        .logo span { color:#00C9A7; }
        .body { padding:2rem; }
        .body h2 { font-size:1.2rem; font-weight:800; color:#111827; margin-bottom:0.75rem; }
        .body p { color:#6B7280; font-size:0.9rem; line-height:1.7; margin-bottom:1rem; }
        .btn { display:inline-block; background:#00C9A7; color:#0A0A14; padding:0.75rem 1.75rem; border-radius:8px; text-decoration:none; font-weight:700; font-size:0.9rem; }
        .footer { background:#F7F8FA; padding:1.25rem 2rem; text-align:center; font-size:0.78rem; color:#9CA3AF; border-top:1px solid #E5E7EB; }
      </style>
    </head>
    <body>
      <div class="wrap">
        <div class="header"><div class="logo">SERV<span>GO</span></div></div>
        <div class="body">
          <h2>Hello, ' . htmlspecialchars($to_name) . '!</h2>
          ' . $body . '
        </div>
        <div class="footer">SERVGO · Omanthai, Vavuniya · info@servgo.com</div>
      </div>
    </body>
    </html>';

    return mail($to_email, $subject, $html, $headers);
}

/* Booking confirmation email */
function emailBookingConfirmed($user_email, $user_name, $service_name, $booking_id) {
    $subject = "✅ Booking Accepted — SERVGO";
    $body = '
      <p>Great news! Your booking has been <strong style="color:#00A888;">accepted</strong> by the service provider.</p>
      <table style="width:100%;background:#F7F8FA;border-radius:8px;padding:1rem;margin-bottom:1.25rem;font-size:0.875rem;">
        <tr><td style="color:#6B7280;padding:0.3rem 0;">Booking ID</td><td style="font-weight:700;text-align:right;">#' . str_pad($booking_id,5,'0',STR_PAD_LEFT) . '</td></tr>
        <tr><td style="color:#6B7280;padding:0.3rem 0;">Service</td><td style="font-weight:700;text-align:right;">' . htmlspecialchars($service_name) . '</td></tr>
      </table>
      <p>The provider will be in touch shortly to confirm timing. You can track your booking anytime.</p>
      <a href="http://localhost/servgo/user/my_bookings.php" class="btn">View My Bookings →</a>';
    return sendEmail($user_email, $user_name, $subject, $body);
}

/* Booking declined email */
function emailBookingDeclined($user_email, $user_name, $service_name) {
    $subject = "❌ Booking Update — SERVGO";
    $body = '
      <p>Unfortunately your booking for <strong>' . htmlspecialchars($service_name) . '</strong> was declined.</p>
      <p>Don\'t worry — you can browse other available providers and book again anytime.</p>
      <a href="http://localhost/servgo/user/services.php" class="btn">Browse Services →</a>';
    return sendEmail($user_email, $user_name, $subject, $body);
}

/* New booking notification to provider */
function emailNewBooking($provider_email, $provider_name, $customer_name, $service_name) {
    $subject = "📋 New Booking Request — SERVGO";
    $body = '
      <p>You have a new booking request!</p>
      <table style="width:100%;background:#F7F8FA;border-radius:8px;padding:1rem;margin-bottom:1.25rem;font-size:0.875rem;">
        <tr><td style="color:#6B7280;padding:0.3rem 0;">Customer</td><td style="font-weight:700;text-align:right;">' . htmlspecialchars($customer_name) . '</td></tr>
        <tr><td style="color:#6B7280;padding:0.3rem 0;">Service</td><td style="font-weight:700;text-align:right;">' . htmlspecialchars($service_name) . '</td></tr>
      </table>
      <p>Log in to your dashboard to accept or decline this request.</p>
      <a href="http://localhost/servgo/provider/bookings.php" class="btn">View Booking →</a>';
    return sendEmail($provider_email, $provider_name, $subject, $body);
}
