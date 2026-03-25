/* ═══════════════════════════════════════════════════
   ARDY REAL ESTATE — HERO SLIDER JS
   hero-slider.js — include only on pages with a hero slider

   Required HTML structure:
     <div id="slidesWrap">
       <div class="slide active"> <img .../> </div>
       <div class="slide"> ... </div>
     </div>
     <div id="slideDots"></div>
     <div id="slideCounter"></div>
═══════════════════════════════════════════════════ */

(function () {
  'use strict';

  const slidesWrap = document.getElementById('slidesWrap');
  if (!slidesWrap) return; // not on this page

  const slides   = slidesWrap.querySelectorAll('.slide');
  const dotsWrap = document.getElementById('slideDots');
  const counter  = document.getElementById('slideCounter');
  let cur = 0, timer;

  // Build dots
  if (dotsWrap) {
    slides.forEach((_, i) => {
      const d = document.createElement('div');
      d.className = 'dot' + (i === 0 ? ' active' : '');
      d.setAttribute('aria-label', 'Go to slide ' + (i + 1));
      d.addEventListener('click', () => { goTo(i); startAuto(); });
      dotsWrap.appendChild(d);
    });
  }

  function goTo(n) {
    slides[cur].classList.remove('active');
    if (dotsWrap) dotsWrap.querySelectorAll('.dot')[cur].classList.remove('active');
    cur = (n + slides.length) % slides.length;
    slides[cur].classList.add('active');
    if (dotsWrap) dotsWrap.querySelectorAll('.dot')[cur].classList.add('active');
    if (counter) counter.textContent =
      String(cur + 1).padStart(2, '0') + ' / ' + String(slides.length).padStart(2, '0');
  }

  function startAuto() {
    clearInterval(timer);
    timer = setInterval(() => goTo(cur + 1), 5000);
  }

  // Arrow keys
  document.addEventListener('keydown', e => {
    if (e.key === 'ArrowRight') { goTo(cur + 1); startAuto(); }
    if (e.key === 'ArrowLeft')  { goTo(cur - 1); startAuto(); }
  });

  // Touch swipe
  let tx = 0;
  slidesWrap.addEventListener('touchstart', e => { tx = e.touches[0].clientX; }, { passive: true });
  slidesWrap.addEventListener('touchend',   e => {
    const dx = e.changedTouches[0].clientX - tx;
    if (Math.abs(dx) > 50) { goTo(dx < 0 ? cur + 1 : cur - 1); startAuto(); }
  }, { passive: true });

  // Initial counter text
  if (counter) counter.textContent = '01 / ' + String(slides.length).padStart(2, '0');

  startAuto();

})();