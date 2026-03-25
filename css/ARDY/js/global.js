/* ═══════════════════════════════════════════════════
   ARDY REAL ESTATE — GLOBAL JS
   global.js — include on every page (before </body>)
═══════════════════════════════════════════════════ */

(function () {
  'use strict';

  /* ────────────────────────────────────────────────
     1. NAV — scroll shadow + hamburger
  ──────────────────────────────────────────────── */
  const nav       = document.getElementById('mainNav');
  const hamburger = document.getElementById('hamburger');
  const navLinks  = document.getElementById('navLinks');

  if (nav) {
    window.addEventListener('scroll', () => {
      nav.classList.toggle('scrolled', window.scrollY > 40);
    }, { passive: true });
  }

  if (hamburger && navLinks) {/* ═══════════════════════════════════════════════════
   ARDY REAL ESTATE — GLOBAL JS
   global.js — include on every page (before </body>)
═══════════════════════════════════════════════════ */

(function () {
  'use strict';

  /* ────────────────────────────────────────────────
     1. NAV — scroll shadow + hamburger
  ──────────────────────────────────────────────── */
  const nav       = document.getElementById('mainNav');
  const hamburger = document.getElementById('hamburger');
  const navLinks  = document.getElementById('navLinks');

  if (nav) {
    window.addEventListener('scroll', () => {
      nav.classList.toggle('scrolled', window.scrollY > 40);
    }, { passive: true });
  }

  if (hamburger && navLinks) {
    hamburger.addEventListener('click', () => {
      const open = navLinks.classList.toggle('open');
      const spans = hamburger.querySelectorAll('span');
      spans[0].style.transform = open ? 'rotate(45deg) translate(4.5px,4.5px)' : '';
      spans[1].style.opacity   = open ? '0' : '1';
      spans[2].style.transform = open ? 'rotate(-45deg) translate(4.5px,-4.5px)' : '';
      hamburger.setAttribute('aria-expanded', open);
    });

    // Close menu on link click
    navLinks.querySelectorAll('a').forEach(a => {
      a.addEventListener('click', () => {
        navLinks.classList.remove('open');
        hamburger.querySelectorAll('span').forEach(s => {
          s.style.transform = '';
          s.style.opacity   = '1';
        });
        hamburger.setAttribute('aria-expanded', 'false');
      });
    });
  }

  /* ────────────────────────────────────────────────
     2. NAV — auto-highlight active page link
  ──────────────────────────────────────────────── */
  if (navLinks) {
    const currentPath = window.location.pathname.split('/').pop() || 'index.html';
    navLinks.querySelectorAll('a').forEach(a => {
      const href = a.getAttribute('href') || '';
      // match page (strip hash)
      const page = href.split('#')[0] || 'index.html';
      if (page === currentPath) a.classList.add('active');
    });
  }

  /* ────────────────────────────────────────────────
     3. SCROLL REVEAL
  ──────────────────────────────────────────────── */
  const io = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.classList.add('in');
        io.unobserve(e.target);
      }
    });
  }, { threshold: 0.07, rootMargin: '0px 0px -30px 0px' });

  document.querySelectorAll('.rv, .rv-l, .rv-r').forEach(el => io.observe(el));

  /* ────────────────────────────────────────────────
     4. CONTACT / QUICK FORM HANDLER
     Works for any <form> with data-ardy-form attr
     Also works for the hero quick-form
  ──────────────────────────────────────────────── */
  window.handleForm = function (form) {
    const btn = form.querySelector('[type="submit"], .hc-btn, .btn-brown');
    if (!btn) return false;
    const orig = btn.textContent;
    btn.textContent = '✓ Message Sent!';
    btn.style.background = 'var(--taupe)';
    btn.disabled = true;
    setTimeout(() => {
      btn.textContent = orig;
      btn.style.background = '';
      btn.disabled = false;
      form.reset();
    }, 3500);
    return false; // prevent default submit
  };

  /* ────────────────────────────────────────────────
     5. FOOTER NEWSLETTER
  ──────────────────────────────────────────────── */
  window.ftSubscribe = function (btn) {
    const input = btn.previousElementSibling;
    if (!input || !input.value.includes('@')) {
      input.style.borderColor = '#c0392b';
      setTimeout(() => (input.style.borderColor = ''), 1500);
      return;
    }
    const orig = btn.textContent;
    btn.textContent = '✓ Subscribed';
    btn.style.background = 'var(--taupe)';
    btn.disabled = true;
    input.value = '';
    setTimeout(() => {
      btn.textContent = orig;
      btn.style.background = '';
      btn.disabled = false;
    }, 3500);
  };

})();
    hamburger.addEventListener('click', () => {
      const open = navLinks.classList.toggle('open');
      const spans = hamburger.querySelectorAll('span');
      spans[0].style.transform = open ? 'rotate(45deg) translate(4.5px,4.5px)' : '';
      spans[1].style.opacity   = open ? '0' : '1';
      spans[2].style.transform = open ? 'rotate(-45deg) translate(4.5px,-4.5px)' : '';
      hamburger.setAttribute('aria-expanded', open);
    });

    // Close menu on link click
    navLinks.querySelectorAll('a').forEach(a => {
      a.addEventListener('click', () => {
        navLinks.classList.remove('open');
        hamburger.querySelectorAll('span').forEach(s => {
          s.style.transform = '';
          s.style.opacity   = '1';
        });
        hamburger.setAttribute('aria-expanded', 'false');
      });
    });
  }

  /* ────────────────────────────────────────────────
     2. NAV — auto-highlight active page link
  ──────────────────────────────────────────────── */
  if (navLinks) {
    const currentPath = window.location.pathname.split('/').pop() || 'index.html';
    navLinks.querySelectorAll('a').forEach(a => {
      const href = a.getAttribute('href') || '';
      // match page (strip hash)
      const page = href.split('#')[0] || 'index.html';
      if (page === currentPath) a.classList.add('active');
    });
  }

  /* ────────────────────────────────────────────────
     3. SCROLL REVEAL
  ──────────────────────────────────────────────── */
  const io = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.classList.add('in');
        io.unobserve(e.target);
      }
    });
  }, { threshold: 0.07, rootMargin: '0px 0px -30px 0px' });

  document.querySelectorAll('.rv, .rv-l, .rv-r').forEach(el => io.observe(el));

  /* ────────────────────────────────────────────────
     4. CONTACT / QUICK FORM HANDLER
     Works for any <form> with data-ardy-form attr
     Also works for the hero quick-form
  ──────────────────────────────────────────────── */
  window.handleForm = function (form) {
    const btn = form.querySelector('[type="submit"], .hc-btn, .btn-brown');
    if (!btn) return false;
    const orig = btn.textContent;
    btn.textContent = '✓ Message Sent!';
    btn.style.background = 'var(--taupe)';
    btn.disabled = true;
    setTimeout(() => {
      btn.textContent = orig;
      btn.style.background = '';
      btn.disabled = false;
      form.reset();
    }, 3500);
    return false; // prevent default submit
  };

  /* ────────────────────────────────────────────────
     5. FOOTER NEWSLETTER
  ──────────────────────────────────────────────── */
  window.ftSubscribe = function (btn) {
    const input = btn.previousElementSibling;
    if (!input || !input.value.includes('@')) {
      input.style.borderColor = '#c0392b';
      setTimeout(() => (input.style.borderColor = ''), 1500);
      return;
    }
    const orig = btn.textContent;
    btn.textContent = '✓ Subscribed';
    btn.style.background = 'var(--taupe)';
    btn.disabled = true;
    input.value = '';
    setTimeout(() => {
      btn.textContent = orig;
      btn.style.background = '';
      btn.disabled = false;
    }, 3500);
  };

})();
/* ═══════════════════════════════════════════════════
   ARDY REAL ESTATE — GLOBAL JS
   global.js — include on every page (before </body>)
═══════════════════════════════════════════════════ */

(function () {
  'use strict';

  /* ── 1. NAV scroll shadow ── */
  const nav = document.getElementById('mainNav');
  if (nav) {
    window.addEventListener('scroll', () => {
      nav.classList.toggle('scrolled', window.scrollY > 40);
    }, { passive: true });
  }

  /* ── 2. Hamburger ── */
  const hamburger = document.getElementById('hamburger');
  const navLinks  = document.getElementById('navLinks');

  if (hamburger && navLinks) {
    hamburger.addEventListener('click', () => {
      const open = navLinks.classList.toggle('open');
      const spans = hamburger.querySelectorAll('span');
      spans[0].style.transform = open ? 'rotate(45deg) translate(4.5px,4.5px)' : '';
      spans[1].style.opacity   = open ? '0' : '1';
      spans[2].style.transform = open ? 'rotate(-45deg) translate(4.5px,-4.5px)' : '';
      hamburger.setAttribute('aria-expanded', open);
    });
  }

  /* ── 3. Services dropdown toggle ── */
  document.querySelectorAll('.has-drop').forEach(li => {
    const link = li.querySelector(':scope > a');
    if (!link) return;

    // Desktop: toggle on click
    link.addEventListener('click', e => {
      e.preventDefault();
      const isOpen = li.classList.contains('open');
      // Close all others
      document.querySelectorAll('.has-drop.open').forEach(o => o.classList.remove('open'));
      if (!isOpen) li.classList.add('open');
    });

    // Close when clicking dropdown items (mobile: let link navigate)
    li.querySelectorAll('.nav-drop a').forEach(a => {
      a.addEventListener('click', () => {
        li.classList.remove('open');
        if (navLinks) {
          navLinks.classList.remove('open');
          if (hamburger) {
            hamburger.querySelectorAll('span').forEach(s => { s.style.transform=''; s.style.opacity='1'; });
            hamburger.setAttribute('aria-expanded','false');
          }
        }
      });
    });
  });

  // Close dropdowns when clicking outside
  document.addEventListener('click', e => {
    if (!e.target.closest('.has-drop')) {
      document.querySelectorAll('.has-drop.open').forEach(o => o.classList.remove('open'));
    }
  });

  // Close mobile nav on non-dropdown link click
  if (navLinks) {
    navLinks.querySelectorAll('li:not(.has-drop) > a').forEach(a => {
      a.addEventListener('click', () => {
        navLinks.classList.remove('open');
        if (hamburger) {
          hamburger.querySelectorAll('span').forEach(s => { s.style.transform=''; s.style.opacity='1'; });
          hamburger.setAttribute('aria-expanded','false');
        }
      });
    });
  }

  /* ── 4. Auto-highlight active page link ── */
  if (navLinks) {
    const currentPage = window.location.pathname.split('/').pop() || 'index.html';
    navLinks.querySelectorAll('a').forEach(a => {
      const href = (a.getAttribute('href') || '').split('#')[0];
      if (href && href === currentPage) a.classList.add('active');
    });
  }

  /* ── 5. Scroll reveal ── */
  const io = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); }
    });
  }, { threshold: 0.07, rootMargin: '0px 0px -30px 0px' });
  document.querySelectorAll('.rv, .rv-l, .rv-r').forEach(el => io.observe(el));

  /* ── 6. Form handler ── */
  window.handleForm = function (form) {
    const btn = form.querySelector('[type="submit"], .hc-btn');
    if (!btn) return false;
    const orig = btn.textContent;
    btn.textContent = '✓ Message Sent!';
    btn.style.background = 'var(--taupe)';
    btn.disabled = true;
    setTimeout(() => { btn.textContent = orig; btn.style.background = ''; btn.disabled = false; form.reset(); }, 3500);
    return false;
  };

  /* ── 7. Newsletter ── */
  window.ftSubscribe = function (btn) {
    const input = btn.previousElementSibling;
    if (!input || !input.value.includes('@')) {
      input.style.borderColor = '#c0392b';
      setTimeout(() => (input.style.borderColor = ''), 1500);
      return;
    }
    const orig = btn.textContent;
    btn.textContent = '✓ Subscribed'; btn.style.background = 'var(--taupe)'; btn.disabled = true; input.value = '';
    setTimeout(() => { btn.textContent = orig; btn.style.background = ''; btn.disabled = false; }, 3500);
  };

})();