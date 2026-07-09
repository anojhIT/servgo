/* ================================================
   SERVGO — main.js
   Features:
   1. Toast notifications
   2. Auto hide PHP alerts
   3. Loading button on form submit
   4. Counter animation (homepage stats)
   5. Live table search (admin)
   6. Confirm before booking / dangerous actions
   7. Active nav link highlight
   8. Mobile hamburger menu
   9. Smooth scroll
   10. Fade in on scroll (cards)
   ================================================ */

document.addEventListener('DOMContentLoaded', function () {

  /* ══════════════════════════════════════════
     1. TOAST NOTIFICATION SYSTEM
     Usage: showToast('Message here', 'success')
     Types: success / error / info
  ══════════════════════════════════════════ */
  function showToast(message, type = 'success') {
    // Create container if not exists
    let container = document.getElementById('toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'toast-container';
      container.style.cssText = `
        position: fixed; bottom: 2rem; right: 2rem;
        z-index: 9999; display: flex; flex-direction: column; gap: 0.75rem;
      `;
      document.body.appendChild(container);
    }

    const colors = {
      success: { bg: '#F0FDF4', border: '#BBF7D0', color: '#16A34A', icon: '✅' },
      error:   { bg: '#FEF2F2', border: '#FECACA', color: '#DC2626', icon: '⚠️' },
      info:    { bg: 'rgba(0,201,167,0.08)', border: 'rgba(0,201,167,0.3)', color: '#00A888', icon: 'ℹ️' },
    };
    const c = colors[type] || colors.success;

    const toast = document.createElement('div');
    toast.style.cssText = `
      background: ${c.bg}; border: 1.5px solid ${c.border}; color: ${c.color};
      padding: 0.85rem 1.25rem; border-radius: 10px;
      font-size: 0.875rem; font-weight: 600; font-family: inherit;
      display: flex; align-items: center; gap: 0.6rem;
      box-shadow: 0 8px 24px rgba(0,0,0,0.12);
      min-width: 260px; max-width: 360px;
      transform: translateX(120%); transition: transform 0.35s cubic-bezier(.22,1,.36,1);
    `;
    toast.innerHTML = `<span>${c.icon}</span><span>${message}</span>`;
    container.appendChild(toast);

    // Slide in
    requestAnimationFrame(() => {
      requestAnimationFrame(() => { toast.style.transform = 'translateX(0)'; });
    });

    // Slide out after 3.5s
    setTimeout(() => {
      toast.style.transform = 'translateX(120%)';
      setTimeout(() => toast.remove(), 400);
    }, 3500);
  }

  // Make showToast available globally
  window.showToast = showToast;


  /* ══════════════════════════════════════════
     2. AUTO HIDE PHP ALERTS
     Finds .alert divs and fades them out
  ══════════════════════════════════════════ */
  document.querySelectorAll('.alert').forEach(function (alert) {
    // Also show as toast
    const isSuccess = alert.classList.contains('alert-success');
    const isError   = alert.classList.contains('alert-error');
    const text      = alert.innerText.trim();

    if (text) {
      showToast(text, isSuccess ? 'success' : isError ? 'error' : 'info');
    }

    // Fade out the inline alert after 4s
    setTimeout(function () {
      alert.style.transition = 'opacity 0.6s, max-height 0.6s, margin 0.6s, padding 0.6s';
      alert.style.opacity    = '0';
      alert.style.maxHeight  = '0';
      alert.style.margin     = '0';
      alert.style.padding    = '0';
      alert.style.overflow   = 'hidden';
    }, 4000);
  });


  /* ══════════════════════════════════════════
     3. LOADING BUTTON ON FORM SUBMIT
     Adds spinner to submit button on click
  ══════════════════════════════════════════ */
  document.querySelectorAll('form').forEach(function (form) {
    form.addEventListener('submit', function () {
      const btn = form.querySelector('button[type="submit"]');
      if (btn) {
        btn.disabled     = true;
        btn.style.opacity = '0.75';
        const original   = btn.innerHTML;
        btn.innerHTML    = '⏳ Please wait...';

        // Re-enable after 5s in case of error
        setTimeout(function () {
          btn.disabled     = false;
          btn.style.opacity = '1';
          btn.innerHTML    = original;
        }, 5000);
      }
    });
  });


  /* ══════════════════════════════════════════
     4. COUNTER ANIMATION (Homepage stats)
     Looks for elements with data-count attribute
  ══════════════════════════════════════════ */
  function animateCounter(el) {
    const target   = parseInt(el.getAttribute('data-count'));
    const duration = 2000;
    const step     = target / (duration / 16);
    let current    = 0;

    const timer = setInterval(function () {
      current += step;
      if (current >= target) {
        current = target;
        clearInterval(timer);
      }
      el.textContent = Math.floor(current).toLocaleString();
    }, 16);
  }

  // Observe stat numbers
  const statNums = document.querySelectorAll('.stat-num');
  if (statNums.length > 0 && 'IntersectionObserver' in window) {
    const observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          const el      = entry.target;
          const numEl   = el.querySelector('[data-count]');
          if (numEl) animateCounter(numEl);
          observer.unobserve(el);
        }
      });
    }, { threshold: 0.5 });

    statNums.forEach(function (el) { observer.observe(el); });
  }


  /* ══════════════════════════════════════════
     5. LIVE TABLE SEARCH (Admin pages)
     Automatically adds search box above tables
  ══════════════════════════════════════════ */
  const tableCards = document.querySelectorAll('.table-card');
  tableCards.forEach(function (card) {
    const tbody = card.querySelector('tbody');
    const header = card.querySelector('.table-card-header');
    if (!tbody || !header) return;

    // Create search input
    const searchWrap = document.createElement('div');
    searchWrap.style.cssText = 'padding: 0.85rem 1.25rem; border-bottom: 1px solid var(--border);';

    const searchInput = document.createElement('input');
    searchInput.type        = 'text';
    searchInput.placeholder = '🔍 Search table...';
    searchInput.className   = 'form-control';
    searchInput.style.cssText = 'max-width: 320px; padding: 0.55rem 0.9rem; font-size:0.85rem;';

    searchWrap.appendChild(searchInput);
    header.insertAdjacentElement('afterend', searchWrap);

    // Filter rows on input
    searchInput.addEventListener('input', function () {
      const filter = this.value.toLowerCase();
      const rows   = tbody.querySelectorAll('tr');
      let visible  = 0;

      rows.forEach(function (row) {
        const text = row.innerText.toLowerCase();
        if (text.includes(filter)) {
          row.style.display = '';
          visible++;
        } else {
          row.style.display = 'none';
        }
      });

      // Show "no results" message
      let noResult = card.querySelector('.no-result-msg');
      if (visible === 0) {
        if (!noResult) {
          noResult = document.createElement('div');
          noResult.className = 'no-result-msg';
          noResult.style.cssText = 'text-align:center; padding:2rem; color:var(--muted); font-size:0.875rem;';
          noResult.textContent   = 'No results found.';
          tbody.parentElement.appendChild(noResult);
        }
      } else {
        if (noResult) noResult.remove();
      }
    });
  });


  /* ══════════════════════════════════════════
     6. CONFIRM BEFORE DANGEROUS ACTIONS
     Adds confirm popup to .confirm-btn links
  ══════════════════════════════════════════ */
  document.querySelectorAll('.confirm-btn').forEach(function (el) {
    el.addEventListener('click', function (e) {
      const msg = el.getAttribute('data-confirm') || 'Are you sure?';
      if (!confirm(msg)) e.preventDefault();
    });
  });

  // Also confirm Book Now buttons in services page
  document.querySelectorAll('a[href*="book="]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      if (!confirm('Confirm booking for this service?')) e.preventDefault();
    });
  });

  // Confirm accept/decline in admin bookings
  document.querySelectorAll('a[href*="accept="]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      if (!confirm('Accept this booking?')) e.preventDefault();
    });
  });
  document.querySelectorAll('a[href*="decline="]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      if (!confirm('Decline this booking?')) e.preventDefault();
    });
  });


  /* ══════════════════════════════════════════
     7. ACTIVE NAV LINK HIGHLIGHT
     Automatically highlights current page link
  ══════════════════════════════════════════ */
  const currentPath = window.location.pathname;
  document.querySelectorAll('.nav-links a, .sidebar-link').forEach(function (link) {
    const linkPath = new URL(link.href, window.location.origin).pathname;
    if (linkPath === currentPath) {
      link.classList.add('active');
    }
  });


  /* ══════════════════════════════════════════
     8. MOBILE HAMBURGER MENU
     Toggles nav-links on small screens
  ══════════════════════════════════════════ */
  const nav = document.querySelector('.nav');
  if (nav) {
    // Create hamburger button
    const hamburger = document.createElement('button');
    hamburger.id    = 'hamburger';
    hamburger.innerHTML = '☰';
    hamburger.style.cssText = `
      display: none; background: none; border: none;
      color: #fff; font-size: 1.5rem; cursor: pointer;
      padding: 0.25rem 0.5rem; line-height: 1;
    `;
    nav.appendChild(hamburger);

    // Show hamburger on mobile
    const style = document.createElement('style');
    style.textContent = `
      @media(max-width:600px) {
        #hamburger { display: block !important; }
        .nav-links.open {
          display: flex !important; flex-direction: column;
          position: fixed; top: 64px; left: 0; right: 0;
          background: rgba(10,10,20,0.98); padding: 1.5rem;
          gap: 0.5rem; z-index: 199; border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .nav-links.open a { padding: 0.75rem 1rem; font-size: 1rem; }
      }
    `;
    document.head.appendChild(style);

    hamburger.addEventListener('click', function () {
      const links = document.querySelector('.nav-links');
      if (links) {
        links.classList.toggle('open');
        hamburger.innerHTML = links.classList.contains('open') ? '✕' : '☰';
      }
    });
  }


  /* ══════════════════════════════════════════
     9. SMOOTH SCROLL
     For any anchor links on the page
  ══════════════════════════════════════════ */
  document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
    anchor.addEventListener('click', function (e) {
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });


  /* ══════════════════════════════════════════
     10. FADE IN ON SCROLL (Cards)
     Cards animate in when they enter viewport
  ══════════════════════════════════════════ */
  const fadeStyle = document.createElement('style');
  fadeStyle.textContent = `
    .svc-card, .why-card, .quick-card, .kpi-card, .browse-card, .step {
      opacity: 0;
      transform: translateY(24px);
      transition: opacity 0.5s ease, transform 0.5s ease;
    }
    .svc-card.visible, .why-card.visible, .quick-card.visible,
    .kpi-card.visible, .browse-card.visible, .step.visible {
      opacity: 1;
      transform: translateY(0);
    }
  `;
  document.head.appendChild(fadeStyle);

  if ('IntersectionObserver' in window) {
    const fadeObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry, i) {
        if (entry.isIntersecting) {
          // Stagger delay based on position
          setTimeout(function () {
            entry.target.classList.add('visible');
          }, i * 80);
          fadeObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1 });

    document.querySelectorAll('.svc-card, .why-card, .quick-card, .kpi-card, .browse-card, .step')
      .forEach(function (el) { fadeObserver.observe(el); });
  } else {
    // Fallback for old browsers - just show everything
    document.querySelectorAll('.svc-card, .why-card, .quick-card, .kpi-card, .browse-card, .step')
      .forEach(function (el) { el.classList.add('visible'); });
  }


  /* ══════════════════════════════════════════
     BONUS: Navbar shadow on scroll
  ══════════════════════════════════════════ */
  window.addEventListener('scroll', function () {
    const nav = document.querySelector('.nav');
    if (nav) {
      if (window.scrollY > 10) {
        nav.style.boxShadow = '0 4px 24px rgba(0,0,0,0.3)';
      } else {
        nav.style.boxShadow = 'none';
      }
    }
  });

});
