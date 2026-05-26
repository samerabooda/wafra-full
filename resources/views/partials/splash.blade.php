{{--
  Page-load splash screen — animated company sphere logo
  انبثاق: sphere scales from 0 → full size with spring overshoot
  تدوير:  continuous 3-D Y-axis rotation (globe-spin effect)
  Fades after 2.2 s, JS removes element on animationend.
--}}

@php
/* ── Generate the exact logo sphere dots ─────────────────────
   Parameters tuned to match the actual logo image:
   rounded-square tiles, dark teal → near-white gradient          */
$sR = 43.0;  $sCx = 50.0; $sCy = 48.0;
$dW =  9.0;  $dH  =  7.4; $dRx = 1.6;
$pX =  10.5; $pY  =  9.2;

/* Logo teal #1B9BA4 → near-white #E6F5F6 */
$r1 = 27;  $g1 = 155; $b1 = 164;
$r2 = 230; $g2 = 245; $b2 = 246;

$splash_dots = [];
$rowY = $sCy - $sR + $dH / 2 + 0.5;
while ($rowY <= $sCy + $sR - $dH / 2 - 0.5) {
    $dy = $rowY - $sCy;
    $hw = sqrt(max(0.0, $sR * $sR - $dy * $dy));
    if ($hw > $dW / 2) {
        $n  = min(
            max(1, (int) floor(($hw * 2 - $dW * 0.4) / $pX) + 1),
            (int) floor($hw * 2 / ($dW + 0.9))
        );
        $sx = $sCx - ($n - 1) * $pX / 2;
        for ($i = 0; $i < $n; $i++) {
            $cx = $sx + $i * $pX;
            $nx = ($cx - ($sCx - $sR)) / ($sR * 2);
            $ny = ($rowY - ($sCy - $sR)) / ($sR * 2);
            /* Light-source: top-right bright, bottom-left dark */
            $lf = max(0.0, min(1.0, $nx * 0.62 + (1 - $ny) * 0.50 - 0.10));
            $splash_dots[] = [
                'x' => round($cx - $dW / 2, 2),
                'y' => round($rowY - $dH / 2, 2),
                'c' => sprintf('rgb(%d,%d,%d)',
                    (int)($r1 + ($r2 - $r1) * $lf),
                    (int)($g1 + ($g2 - $g1) * $lf),
                    (int)($b1 + ($b2 - $b1) * $lf)),
            ];
        }
    }
    $rowY += $pY;
}
@endphp

{{-- ── Splash overlay ─────────────────────────────────────── --}}
<div id="wfr-splash" aria-hidden="true">
  <div id="wfr-splash-inner">

    {{-- ── Sphere (tiles only — no text) ── --}}
    <div id="wfr-sphere-stage">

      {{-- Orbit swoosh ring (dark teal arc — sweeps around the sphere) --}}
      <div id="wfr-splash-ring-wrap">
        <div id="wfr-splash-ring"></div>
      </div>

      {{-- SVG sphere: teal rounded-square tiles --}}
      <svg id="wfr-sphere-svg"
           viewBox="0 0 100 100"
           xmlns="http://www.w3.org/2000/svg"
           aria-hidden="true">
        <defs>
          <clipPath id="wfr_sp_clip">
            <circle cx="{{ $sCx }}" cy="{{ $sCy }}" r="{{ $sR + 0.5 }}"/>
          </clipPath>
          {{-- Radial glow overlay (brighten centre slightly) --}}
          <radialGradient id="wfr_sp_glow" cx="62%" cy="38%" r="55%">
            <stop offset="0%"   stop-color="#FFFFFF" stop-opacity="0.12"/>
            <stop offset="100%" stop-color="#FFFFFF" stop-opacity="0"/>
          </radialGradient>
        </defs>
        <g clip-path="url(#wfr_sp_clip)">
          @foreach($splash_dots as $d)
          <rect x="{{ $d['x'] }}" y="{{ $d['y'] }}"
                width="{{ $dW }}" height="{{ $dH }}"
                rx="{{ $dRx }}" fill="{{ $d['c'] }}"/>
          @endforeach
          {{-- Subtle light-source sheen --}}
          <circle cx="{{ $sCx }}" cy="{{ $sCy }}" r="{{ $sR }}"
                  fill="url(#wfr_sp_glow)"/>
        </g>
      </svg>

    </div>{{-- #wfr-sphere-stage --}}

    {{-- Loading dots --}}
    <div id="wfr-splash-dots">
      <span></span><span></span><span></span>
    </div>

  </div>{{-- #wfr-splash-inner --}}
</div>{{-- #wfr-splash --}}

<style>
/* ══════════════════════════════════════════════════════════
   SPLASH  —  انبثاق (emerge) + تدوير (spin)
   ══════════════════════════════════════════════════════════ */

/* ── Overlay ─────────────────────────────────────────────── */
#wfr-splash {
  position       : fixed;
  inset          : 0;
  z-index        : 99999;
  background     : linear-gradient(145deg, #060D1B 0%, #0C1830 55%, #0E2040 100%);
  display        : flex;
  align-items    : center;
  justify-content: center;
  /* After 2.2 s → fade out over 0.4 s */
  animation      : wfr-out 0.4s ease-in 2.2s forwards;
  pointer-events : all;
}
@keyframes wfr-out {
  to { opacity: 0; visibility: hidden; pointer-events: none; }
}

/* ── Inner container: انبثاق ───────────────────────────────── */
#wfr-splash-inner {
  display        : flex;
  flex-direction : column;
  align-items    : center;
  gap            : 28px;
  animation      : wfr-emerge 0.75s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}
@keyframes wfr-emerge {
  0%   { opacity: 0; transform: scale(0.15) rotate(-25deg); }
  60%  { opacity: 1; }
  100% { opacity: 1; transform: scale(1)    rotate(0deg);   }
}

/* ── Sphere stage: 3-D perspective container ─────────────── */
#wfr-sphere-stage {
  position        : relative;
  width           : 190px;
  height          : 190px;
  perspective     : 600px;
  transform-style : preserve-3d;
}

/* ── SVG sphere: تدوير (Y-axis globe spin) ─────────────────── */
#wfr-sphere-svg {
  position        : absolute;
  inset           : 0;
  width           : 100%;
  height          : 100%;
  animation       : wfr-globe-spin 3.6s linear infinite;
  transform-origin: 50% 50%;
  /* Drop shadow to make it pop on dark background */
  filter          : drop-shadow(0 6px 24px rgba(27,155,164,.45))
                    drop-shadow(0 0  12px rgba(27,155,164,.25));
}
@keyframes wfr-globe-spin {
  from { transform: perspective(600px) rotateY(0deg); }
  to   { transform: perspective(600px) rotateY(360deg); }
}

/* ── Orbit ring: sweeps back→top→front ──────────────────── */
#wfr-splash-ring-wrap {
  position        : absolute;
  width           : 155%;
  height          : 155%;
  top             : -27.5%;
  left            : -27.5%;
  transform-style : preserve-3d;
  perspective     : calc(190px * 5);
  pointer-events  : none;
  z-index         : 2;
}
#wfr-splash-ring {
  position           : absolute;
  inset              : 0;
  border-radius      : 50%;
  border             : 6px solid transparent;
  border-top-color   : #0C7A84;
  border-left-color  : rgba(12,122,132,.65);
  border-right-color : rgba(12,122,132,.40);
  filter             : drop-shadow(0 0 6px rgba(12,122,132,.50));
  animation          : wfr-orbit 3.6s cubic-bezier(.37,0,.63,1) infinite;
  transform-origin   : 50% 50%;
}
@keyframes wfr-orbit {
  0%   { transform: rotateX(-88deg) rotateZ( 6deg); opacity: .05; }
  15%  { transform: rotateX(-54deg) rotateZ( 3deg); opacity: .55; }
  35%  { transform: rotateX(-16deg) rotateZ( 1deg); opacity: .95; }
  50%  { transform: rotateX(  8deg) rotateZ(-1deg); opacity: 1.0; }
  65%  { transform: rotateX( 32deg) rotateZ(-3deg); opacity: .80; }
  82%  { transform: rotateX( 64deg) rotateZ(-5deg); opacity: .30; }
  100% { transform: rotateX( 94deg) rotateZ(-8deg); opacity: .05; }
}

/* ── Loading dots ────────────────────────────────────────── */
#wfr-splash-dots {
  display : flex;
  gap     : 8px;
}
#wfr-splash-dots span {
  width        : 7px;
  height       : 7px;
  border-radius: 50%;
  background   : rgba(27,155,164, .65);
  animation    : wfr-dot 0.7s ease-in-out infinite alternate;
}
#wfr-splash-dots span:nth-child(2) { animation-delay: 0.22s; }
#wfr-splash-dots span:nth-child(3) { animation-delay: 0.44s; }
@keyframes wfr-dot {
  from { opacity: 0.2; transform: scale(0.7); }
  to   { opacity: 1.0; transform: scale(1.2); }
}

/* Prevent scroll flicker while splash is up */
body.wfr-loading { overflow: hidden; }
</style>

<script>
/* Remove #wfr-splash after fade-out animation ends */
(function () {
  document.body.classList.add('wfr-loading');
  var el = document.getElementById('wfr-splash');
  if (!el) return;
  el.addEventListener('animationend', function (e) {
    if (e.animationName === 'wfr-out') {
      el.remove();
      document.body.classList.remove('wfr-loading');
    }
  });
})();
</script>
