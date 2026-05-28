{{--
  Wafra Gulf — Splash Screen v2.0
  • Real logo.png — animated pop-in
  • Typewriter: "وفرة الخليجية" letter by letter
  • Disappears when page is fully loaded (min 3.8s)
--}}

{{-- ── Splash overlay ───────────────────────────────────── --}}
<div id="wfr-splash" aria-hidden="true">

  {{-- Background particles --}}
  <div class="wfr-particles">
    <span></span><span></span><span></span>
    <span></span><span></span><span></span>
  </div>

  {{-- Center content --}}
  <div id="wfr-splash-inner">

    {{-- Logo frame --}}
    <div id="wfr-logo-wrap">
      <div id="wfr-logo-frame">
        <img src="{{ asset('logo.png') }}" id="wfr-logo-img" alt="وفرة الخليجية">
      </div>
      {{-- Expanding rings --}}
      <div class="wfr-ring wfr-ring-1"></div>
      <div class="wfr-ring wfr-ring-2"></div>
      <div class="wfr-ring wfr-ring-3"></div>
    </div>

    {{-- Company name typewriter --}}
    <div id="wfr-company-wrap">
      <div id="wfr-type-line" dir="rtl">
        <span id="wfr-typed-text"></span><span id="wfr-cursor">|</span>
      </div>
      <div id="wfr-tagline">للخدمات المالية</div>
    </div>

    {{-- Progress bar --}}
    <div id="wfr-progress-track">
      <div id="wfr-progress-bar"></div>
    </div>

  </div>
</div>

<style>
/* ══════════════════════════════════════════════════════════
   SPLASH — Logo pop + Typewriter name
   ══════════════════════════════════════════════════════════ */

#wfr-splash {
  position       : fixed;
  inset          : 0;
  z-index        : 99999;
  background     : radial-gradient(ellipse at 30% 30%, #0D1E35 0%, #060D1B 60%, #050A14 100%);
  display        : flex;
  align-items    : center;
  justify-content: center;
  overflow       : hidden;
  pointer-events : all;
  transition     : opacity .5s ease, visibility .5s ease;
}
#wfr-splash.wfr-hiding {
  opacity    : 0;
  visibility : hidden;
  pointer-events: none;
}

/* ── Background floating particles ── */
.wfr-particles { position:absolute; inset:0; overflow:hidden; pointer-events:none; }
.wfr-particles span {
  position:absolute; border-radius:50%;
  background:radial-gradient(circle, rgba(26,173,186,.18) 0%, transparent 70%);
  animation:wfr-float linear infinite;
}
.wfr-particles span:nth-child(1){width:320px;height:320px;top:-80px;right:-60px; animation-duration:18s;opacity:.7}
.wfr-particles span:nth-child(2){width:240px;height:240px;bottom:-60px;left:-40px;animation-duration:22s;animation-delay:-7s;opacity:.5}
.wfr-particles span:nth-child(3){width:160px;height:160px;top:40%;left:10%;    animation-duration:15s;animation-delay:-4s;opacity:.3}
.wfr-particles span:nth-child(4){width:100px;height:100px;bottom:20%;right:15%;animation-duration:12s;animation-delay:-9s;opacity:.25}
.wfr-particles span:nth-child(5){width:200px;height:200px;top:20%;right:20%;  animation-duration:20s;animation-delay:-2s;opacity:.2}
.wfr-particles span:nth-child(6){width:80px; height:80px; top:60%;left:60%;  animation-duration:9s; animation-delay:-5s;opacity:.15}
@keyframes wfr-float {
  0%  { transform:translateY(0)   scale(1);   }
  50% { transform:translateY(-30px) scale(1.06); }
  100%{ transform:translateY(0)   scale(1);   }
}

/* ── Inner wrapper ── */
#wfr-splash-inner {
  display        : flex;
  flex-direction : column;
  align-items    : center;
  gap            : 24px;
  animation      : wfr-emerge .65s cubic-bezier(.34,1.56,.64,1) both;
}
@keyframes wfr-emerge {
  from { opacity:0; transform:scale(.5) translateY(40px); }
  to   { opacity:1; transform:none; }
}

/* ── Logo outer wrapper (holds rings) ── */
#wfr-logo-wrap {
  position:relative;
  width:200px; height:200px;
  display:flex; align-items:center; justify-content:center;
}

/* ── Logo white frame ── */
#wfr-logo-frame {
  width:190px; height:190px;
  background:white;
  border-radius:34px;
  display:flex; align-items:center; justify-content:center;
  overflow:hidden;
  position:relative; z-index:2;
  animation:
    wfr-logo-pop   .85s cubic-bezier(.34,1.56,.64,1) .15s both,
    wfr-logo-glow  2.4s ease-in-out 1.2s infinite;
}
#wfr-logo-img {
  width:174px; height:174px;
  object-fit:contain;
}

@keyframes wfr-logo-pop {
  0%  { transform:scale(0) rotate(-22deg); opacity:0; filter:blur(18px);
        box-shadow:0 0 0 rgba(26,173,186,0); }
  65% { transform:scale(1.14) rotate(4deg); opacity:1; filter:blur(0);
        box-shadow:0 20px 70px rgba(26,173,186,.8); }
  80% { transform:scale(0.93) rotate(-1deg); }
  90% { transform:scale(1.04); }
  100%{ transform:scale(1) rotate(0); opacity:1; filter:blur(0);
        box-shadow:0 12px 48px rgba(26,173,186,.45), 0 4px 16px rgba(0,0,0,.35); }
}
@keyframes wfr-logo-glow {
  0%,100%{ box-shadow:0 12px 48px rgba(26,173,186,.45), 0 4px 16px rgba(0,0,0,.35); }
  50%    { box-shadow:0 18px 72px rgba(26,173,186,.75), 0 0 120px rgba(26,173,186,.22); }
}

/* ── Expanding rings ── */
.wfr-ring {
  position:absolute; inset:0;
  border-radius:34px;
  border:2.5px solid rgba(26,173,186,.6);
  animation:wfr-ring-burst 2.8s ease-out infinite;
  pointer-events:none;
}
.wfr-ring-2 { animation-delay:.55s;  border-color:rgba(26,173,186,.4); }
.wfr-ring-3 { animation-delay:1.1s;  border-color:rgba(26,173,186,.25); border-width:1.5px; }
@keyframes wfr-ring-burst {
  0%  { transform:scale(1);   opacity:.85; }
  100%{ transform:scale(1.85);opacity:0;   }
}

/* ── Typewriter section ── */
#wfr-company-wrap {
  text-align:center;
  direction:rtl;
}
#wfr-type-line {
  font-family:'Tajawal','Cairo',sans-serif;
  font-size:2.4rem;
  font-weight:900;
  color:#E6EFF6;
  letter-spacing:.5px;
  min-height:3rem;
  direction:rtl;
  text-shadow:0 2px 20px rgba(26,173,186,.3);
}
#wfr-cursor {
  color:#1AADBA;
  font-weight:300;
  animation:wfr-blink .55s step-end infinite;
  text-shadow:0 0 12px rgba(26,173,186,.8);
}
@keyframes wfr-blink { 0%,100%{opacity:1} 50%{opacity:0} }

#wfr-tagline {
  font-family:'Tajawal','Cairo',sans-serif;
  font-size:1.05rem;
  color:#5A80A0;
  font-weight:500;
  margin-top:6px;
  letter-spacing:.3px;
  opacity:0;
  transition:opacity .7s ease;
}
#wfr-tagline.visible { opacity:1; }

/* ── Progress bar ── */
#wfr-progress-track {
  width:180px; height:3px;
  background:rgba(26,173,186,.12);
  border-radius:2px;
  overflow:hidden;
  margin-top:4px;
}
#wfr-progress-bar {
  height:100%;
  width:0%;
  background:linear-gradient(90deg, #0E7A88, #1AADBA, #22C4D4);
  border-radius:2px;
  transition:width .08s linear;
  box-shadow:0 0 8px rgba(26,173,186,.6);
}

/* Lock scroll */
body.wfr-loading { overflow:hidden; }
</style>

<script>
(function(){
  'use strict';

  var splash    = document.getElementById('wfr-splash');
  if (!splash) return;

  // ── Skip splash on normal page navigation (only show once per session) ──
  if (sessionStorage.getItem('wfr_shown')) {
    splash.style.display = 'none';
    document.body.classList.remove('wfr-loading');
    return;
  }
  sessionStorage.setItem('wfr_shown', '1');

  document.body.classList.add('wfr-loading');

  var typedEl   = document.getElementById('wfr-typed-text');
  var cursor    = document.getElementById('wfr-cursor');
  var tagline   = document.getElementById('wfr-tagline');
  var progBar   = document.getElementById('wfr-progress-bar');

  /* ── Typewriter ── */
  var lang = localStorage.getItem('wg_lang') || 'ar';
  if (tagline) tagline.textContent = lang === 'en' ? 'Commission Cards Management System' : 'نظام إدارة كروت العمولات';
  var nameAr   = lang === 'en' ? 'Wafra Gulf Financial Services' : 'وفرة الخليجية للخدمات المالية';
  var charDelay = lang === 'en' ? 75 : 95;  /* ms per character */
  var typeStart = 900;   /* delay before typing begins (ms) */
  var charIdx   = 0;

  /* Progress bar fills while typing */
  function updateProgress(pct) {
    if (progBar) progBar.style.width = pct + '%';
  }

  setTimeout(function startTyping() {
    (function typeNext() {
      if (charIdx < nameAr.length) {
        typedEl.textContent += nameAr[charIdx];
        charIdx++;
        var pct = Math.round((charIdx / nameAr.length) * 80);
        updateProgress(pct);
        setTimeout(typeNext, charDelay);
      } else {
        /* Typing done — hide cursor, show tagline */
        setTimeout(function(){
          cursor.style.display = 'none';
          tagline.classList.add('visible');
          updateProgress(100);
        }, 350);
      }
    })();
  }, typeStart);

  /* ── Dismiss on page load, min 3.8s ── */
  var MIN_MS   = 3800;
  var startedAt = Date.now();
  var dismissed = false;

  function hideSplash() {
    if (dismissed) return;
    dismissed = true;
    var elapsed = Date.now() - startedAt;
    var wait    = Math.max(0, MIN_MS - elapsed);
    setTimeout(function(){
      if (splash) {
        splash.classList.add('wfr-hiding');
        setTimeout(function(){
          splash.remove();
          document.body.classList.remove('wfr-loading');
        }, 520);
      }
    }, wait);
  }

  if (document.readyState === 'complete') {
    hideSplash();
  } else {
    window.addEventListener('load', hideSplash);
    /* Safety fallback */
    setTimeout(hideSplash, 7000);
  }
})();
</script>
