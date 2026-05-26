{{--
  Page-load splash screen
  انبثاق : sphere emerges from nothing (scale 0 → 1 spring)
  تدوير  : continuous Y-axis globe-spin
  Exact logo: 3/4 sphere (bottom-right quadrant absent)
              + dark-teal diagonal swoosh band
  Fades after 2.2 s, JS removes element on animationend.
--}}

@php
/* ── Sphere geometry — identical to globe.blade.php ── */
$sR  = 44.0; $sCx = 50.0; $sCy = 46.0;
$dW  =  9.0; $dH  =  7.4; $dRx = 1.6;
$pX  = 10.5; $pY  =  9.2;

/* Logo teal #1B9BA4 → near-white #E6F5F6 */
$r1=27;  $g1=155; $b1=164;
$r2=230; $g2=245; $b2=246;

/* 3/4 clip coordinates */
$botX = $sCx;              $botY = $sCy + $sR + 0.5;
$rgtX = $sCx + $sR + 0.5; $rgtY = $sCy;

/* Swoosh parameters */
$swRx = $sR;
$swRy = round($sR * 0.44, 1);   // ≈ 19.4
$swSW = round($sR * 0.26, 1);   // ≈ 11.4
$swRot = -42;

/* ── Tile generation ─────────────────────────────── */
$sp_dots = [];
$rowY = $sCy - $sR + $dH / 2 + 0.5;
while ($rowY <= $sCy + $sR - $dH / 2 - 0.5) {
    $dy = $rowY - $sCy;
    $hw = sqrt(max(0.0, $sR * $sR - $dy * $dy));
    if ($hw > $dW / 2) {
        $n  = min(
            max(1, (int) floor(($hw * 2 - $dW * 0.4) / $pX) + 1),
            (int) floor($hw * 2 / ($dW + 0.8))
        );
        $sx = $sCx - ($n - 1) * $pX / 2;
        for ($i = 0; $i < $n; $i++) {
            $cx = $sx + $i * $pX;
            $nx = ($cx - ($sCx - $sR)) / ($sR * 2);
            $ny = ($rowY - ($sCy - $sR)) / ($sR * 2);
            $lf = max(0.0, min(1.0, $nx * 0.62 + (1 - $ny) * 0.50 - 0.10));
            $sp_dots[] = [
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

    {{-- Sphere stage (perspective container for 3-D spin) --}}
    <div id="wfr-sphere-stage">

      {{-- Animated orbit swoosh ring (back → top → front) --}}
      <div id="wfr-splash-ring-wrap">
        <div id="wfr-splash-ring"></div>
      </div>

      {{-- 3/4 sphere SVG: teal tiles + diagonal swoosh --}}
      <svg id="wfr-sphere-svg"
           viewBox="0 0 100 100"
           xmlns="http://www.w3.org/2000/svg"
           aria-hidden="true">
        <defs>
          {{-- 3/4 sphere clip: remove bottom-right quadrant         --}}
          {{-- Start bottom → arc CCW 270° → right → center → close --}}
          <clipPath id="wfr_sp_clip">
            <path d="M {{ $botX }},{{ $botY }}
                     A {{ $sR+0.5 }},{{ $sR+0.5 }} 0 1,0 {{ $rgtX }},{{ $rgtY }}
                     L {{ $sCx }},{{ $sCy }} Z"/>
          </clipPath>
          {{-- Radial glow: upper-right light source --}}
          <radialGradient id="wfr_sp_glow" cx="65%" cy="35%" r="52%">
            <stop offset="0%"   stop-color="#FFFFFF" stop-opacity="0.16"/>
            <stop offset="100%" stop-color="#FFFFFF" stop-opacity="0"/>
          </radialGradient>
        </defs>

        <g clip-path="url(#wfr_sp_clip)">
          {{-- Teal tiles --}}
          @foreach($sp_dots as $d)
          <rect x="{{ $d['x'] }}" y="{{ $d['y'] }}"
                width="{{ $dW }}" height="{{ $dH }}"
                rx="{{ $dRx }}" fill="{{ $d['c'] }}"/>
          @endforeach

          {{-- Diagonal swoosh (dark teal band — signature logo element) --}}
          <ellipse cx="{{ $sCx }}" cy="{{ $sCy }}"
                   rx="{{ $swRx }}" ry="{{ $swRy }}"
                   stroke="#0B6B74" stroke-width="{{ $swSW }}"
                   fill="none" opacity="0.82"
                   transform="rotate({{ $swRot }}, {{ $sCx }}, {{ $sCy }})"/>

          {{-- Light sheen --}}
          <circle cx="{{ $sCx }}" cy="{{ $sCy }}" r="{{ $sR }}"
                  fill="url(#wfr_sp_glow)"/>
        </g>
      </svg>

    </div>{{-- #wfr-sphere-stage --}}

    {{-- Loading dots --}}
    <div id="wfr-splash-dots">
      <span></span><span></span><span></span>
    </div>

  </div>
</div>

<style>
/* ══════════════════════════════════════════════════════════
   SPLASH — انبثاق (emerge) + تدوير (spin)
   3/4 sphere + diagonal swoosh — matches exact logo
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
  animation      : wfr-out 0.4s ease-in 2.2s forwards;
  pointer-events : all;
}
@keyframes wfr-out {
  to { opacity:0; visibility:hidden; pointer-events:none; }
}

/* ── Inner: انبثاق (spring emergence) ────────────────────── */
#wfr-splash-inner {
  display        : flex;
  flex-direction : column;
  align-items    : center;
  gap            : 30px;
  animation      : wfr-emerge 0.7s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}
@keyframes wfr-emerge {
  0%   { opacity:0; transform:scale(0.1) rotate(-30deg); }
  55%  { opacity:1; }
  100% { opacity:1; transform:scale(1)   rotate(0deg);   }
}

/* ── Sphere stage: 3-D perspective ──────────────────────── */
#wfr-sphere-stage {
  position        : relative;
  width           : 200px;
  height          : 200px;
  perspective     : 700px;
  transform-style : preserve-3d;
}

/* ── SVG sphere: تدوير (Y-axis globe spin) ──────────────── */
#wfr-sphere-svg {
  position        : absolute;
  inset           : 0;
  width           : 100%;
  height          : 100%;
  transform-origin: 50% 48%;    /* spin around sphere centre */
  animation       : wfr-globe-spin 4s linear infinite;
  /* Teal glow on dark background */
  filter          : drop-shadow(0  8px 28px rgba(27,155,164,.50))
                    drop-shadow(0  0  14px rgba(27,155,164,.30));
}
@keyframes wfr-globe-spin {
  from { transform: perspective(700px) rotateY(0deg);   }
  to   { transform: perspective(700px) rotateY(360deg); }
}

/* ── Orbit ring: swoosh sweeps back→top→front ───────────── */
#wfr-splash-ring-wrap {
  position        : absolute;
  width           : 155%;
  height          : 155%;
  top             : -27.5%;
  left            : -27.5%;
  transform-style : preserve-3d;
  perspective     : 1000px;
  pointer-events  : none;
  z-index         : 2;
}
#wfr-splash-ring {
  position           : absolute;
  inset              : 0;
  border-radius      : 50%;
  border             : 7px solid transparent;
  border-top-color   : #0C7A84;
  border-left-color  : rgba(12,122,132,.65);
  border-right-color : rgba(12,122,132,.38);
  filter             : drop-shadow(0 0 8px rgba(12,122,132,.55));
  animation          : wfr-orbit 4s cubic-bezier(.37,0,.63,1) infinite;
}
@keyframes wfr-orbit {
  0%   { transform:rotateX(-88deg) rotateZ( 6deg); opacity:.05; }
  18%  { transform:rotateX(-52deg) rotateZ( 3deg); opacity:.58; }
  38%  { transform:rotateX(-15deg) rotateZ( 1deg); opacity:.96; }
  50%  { transform:rotateX(  8deg) rotateZ(-1deg); opacity:1.0; }
  65%  { transform:rotateX( 32deg) rotateZ(-3deg); opacity:.78; }
  82%  { transform:rotateX( 62deg) rotateZ(-5deg); opacity:.28; }
  100% { transform:rotateX( 92deg) rotateZ(-8deg); opacity:.05; }
}

/* ── Loading dots ────────────────────────────────────────── */
#wfr-splash-dots {
  display: flex;
  gap    : 9px;
}
#wfr-splash-dots span {
  width        : 7px;
  height       : 7px;
  border-radius: 50%;
  background   : rgba(27,155,164,.7);
  animation    : wfr-dot 0.7s ease-in-out infinite alternate;
}
#wfr-splash-dots span:nth-child(2) { animation-delay:.22s; }
#wfr-splash-dots span:nth-child(3) { animation-delay:.44s; }
@keyframes wfr-dot {
  from { opacity:.2; transform:scale(.7); }
  to   { opacity:1;  transform:scale(1.2); }
}

/* Lock scroll during splash */
body.wfr-loading { overflow:hidden; }
</style>

<script>
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
