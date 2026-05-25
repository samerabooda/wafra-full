{{--
  Page-load splash screen — shows the animated globe logo for 2 seconds,
  then smoothly fades out revealing the page beneath.
  Plays on EVERY page load (the globe animation is the main effect).
--}}

{{-- Splash overlay --}}
<div id="wfr-splash" aria-hidden="true">
  <div id="wfr-splash-inner">
    @include('partials.globe', ['size'=>'xl', 'showText'=>true, 'gid'=>'splash', 'whiteBg'=>true])
    <div id="wfr-splash-dots">
      <span></span><span></span><span></span>
    </div>
  </div>
</div>

<style>
/* ── Splash overlay ──────────────────────────────────── */
#wfr-splash {
  position: fixed;
  inset: 0;
  z-index: 99999;
  background: linear-gradient(145deg, #060D1B 0%, #0C1830 55%, #0E2040 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  /* After 2.2s (globe done) → fade out over 0.4s */
  animation: wfr-splash-out 0.4s ease-in 2.2s forwards;
  pointer-events: all;
}
#wfr-splash-inner {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 18px;
  /* Entrance: scale up from 0.85 */
  animation: wfr-splash-in 0.5s cubic-bezier(.34,1.56,.64,1) both;
}
@keyframes wfr-splash-in  { from { transform:scale(.85); opacity:0; } to { transform:scale(1); opacity:1; } }
@keyframes wfr-splash-out { to   { opacity:0; visibility:hidden; pointer-events:none; } }

/* ── Loading dots ────────────────────────────────────── */
#wfr-splash-dots {
  display: flex;
  gap: 7px;
  margin-top: 4px;
}
#wfr-splash-dots span {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: rgba(90,205,230,.55);
  animation: wfr-dot-pulse 0.7s ease-in-out infinite alternate;
}
#wfr-splash-dots span:nth-child(2) { animation-delay: 0.22s; }
#wfr-splash-dots span:nth-child(3) { animation-delay: 0.44s; }
@keyframes wfr-dot-pulse {
  from { opacity: 0.2; transform: scale(0.7); }
  to   { opacity: 1;   transform: scale(1.1); }
}

/* Prevent scroll flicker while splash is up */
body.wfr-loading { overflow: hidden; }
</style>

<script>
/* Remove overflow lock once splash fades, re-enable scroll */
(function(){
  document.body.classList.add('wfr-loading');
  var el = document.getElementById('wfr-splash');
  if (!el) return;
  /* After the animation ends (2.6s total), remove the element */
  el.addEventListener('animationend', function(e){
    if (e.animationName === 'wfr-splash-out') {
      el.remove();
      document.body.classList.remove('wfr-loading');
    }
  });
})();
</script>
