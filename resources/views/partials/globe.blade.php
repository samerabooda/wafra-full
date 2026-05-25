{{--
  ╔══════════════════════════════════════════════════════════╗
  ║  Wafra Gulf — Animated Dot-Globe Logo  v7.0              ║
  ║  Dot-tile sphere matching the official logo exactly      ║
  ║  Teal #1B9BA4 · dark-arc orbit · charcoal text          ║
  ║  Usage:                                                  ║
  ║    @include('partials.globe', [                          ║
  ║      'size'     => 'xs|sm|md|lg|xl',  (default: 'md')   ║
  ║      'showText' => true|false,         (default: true)   ║
  ║      'gid'      => 'myUniqueId',       (optional)        ║
  ║      'darkText' => true|false,         (default: false)  ║
  ║    ])                                                     ║
  ╚══════════════════════════════════════════════════════════╝
--}}
@php
/* ── Size / text params ───────────────────────────────── */
$gid     = isset($gid) ? preg_replace('/[^a-z0-9]/i','_',$gid) : ('wfg_'.rand(1000,9999));
$sz      = $size     ?? 'md';
$gw      = match($sz){ 'xs'=>42,'sm'=>58,'lg'=>128,'xl'=>172, default=>96 };
$hasText = $showText ?? true;
$dark    = $darkText ?? false;
$nm_fs   = max(10, (int)round($gw * 0.20));
$tg_fs   = max(7,  (int)round($gw * 0.120));
$gap     = max(5,  (int)round($gw * 0.09));

/* ── Dot-globe generation ─────────────────────────────── */
/* SVG viewBox is 0 0 100 100, sphere centred at (50,48) r=43 */
$sR  = 43.0;  $sCx = 50.0;  $sCy = 48.0;
$dW  = 8.0;   $dH  = 6.6;   $dRx = 1.7;
$pX  = 9.8;   $pY  = 8.8;
/* Logo teal: #1B9BA4  →  near-white: #E2F1F2 */
$r1=27;  $g1=155; $b1=164;   // teal
$r2=226; $g2=241; $b2=242;   // near-white

$dots = [];
$rowY = $sCy - $sR + $dH/2 + 0.5;
while ($rowY <= $sCy + $sR - $dH/2 - 0.5) {
    $dy    = $rowY - $sCy;
    $halfW = sqrt(max(0.0, $sR*$sR - $dy*$dy));
    if ($halfW > $dW/2) {
        $n = min(
            max(1, (int)floor(($halfW*2 - $dW*0.4) / $pX) + 1),
            (int)floor($halfW * 2 / ($dW + 0.8))
        );
        $startX = $sCx - ($n-1)*$pX/2;
        for ($i=0; $i<$n; $i++) {
            $cx = $startX + $i*$pX;
            /* lightness: upper-right = bright white, lower-left = full teal */
            $nx = ($cx - ($sCx-$sR)) / ($sR*2);  // 0=left … 1=right
            $ny = ($rowY - ($sCy-$sR)) / ($sR*2); // 0=top  … 1=bottom
            $lf = max(0.0, min(1.0, $nx*0.62 + (1-$ny)*0.50 - 0.10));
            $dots[] = [
                'x' => round($cx - $dW/2, 2),
                'y' => round($rowY - $dH/2, 2),
                'c' => sprintf('rgb(%d,%d,%d)',
                    (int)($r1+($r2-$r1)*$lf),
                    (int)($g1+($g2-$g1)*$lf),
                    (int)($b1+($b2-$b1)*$lf)),
            ];
        }
    }
    $rowY += $pY;
}
@endphp

<style>
/* ═══════════════════════════════════════════════════════
   WFG Dot-Globe v7 — scoped to #{{$gid}}
   ═══════════════════════════════════════════════════════ */
#{{$gid}} {
  --gw : {{ $gw }}px;
  display       : inline-flex;
  flex-direction: column;
  align-items   : center;
  gap           : {{ $gap }}px;
  animation     : wfgIn_{{$gid}} .7s cubic-bezier(.34,1.56,.64,1) both;
}
@keyframes wfgIn_{{$gid}} {
  from { opacity:0; transform:scale(.65) translateY(8px); }
  to   { opacity:1; transform:none; }
}

/* ── Scene (perspective + overflow room for orbit ring) ─ */
#{{$gid}} .wfg-scene {
  position : relative;
  width    : var(--gw);
  height   : var(--gw);
  overflow : visible;
}

/* ── SVG dot globe ───────────────────────────────────── */
#{{$gid}} .wfg-dots {
  position: absolute;
  inset    : 0;
  width    : 100%;
  height   : 100%;
  overflow : visible;
}

/* ══════════════════════════════════════════════════════
   ORBIT ARC — dark teal, back→top→front animation
   Mimics the diagonal swoosh band in the official logo
   ══════════════════════════════════════════════════════ */
#{{$gid}} .wfg-orbit-wrap {
  position        : absolute;
  width           : calc(var(--gw) * 1.54);
  height          : calc(var(--gw) * 1.54);
  top             : calc(var(--gw) * -.27);
  left            : calc(var(--gw) * -.27);
  transform-style : preserve-3d;
  perspective     : calc(var(--gw) * 5);
  pointer-events  : none;
}
#{{$gid}} .wfg-orbit-ring {
  position          : absolute;
  inset             : 0;
  border-radius     : 50%;
  /* Dark teal matching the logo swoosh */
  border            : calc(max(2.5px, var(--gw)*.034)) solid transparent;
  border-top-color  : rgba(13, 122, 130, 1.00);
  border-left-color : rgba(13, 122, 130, .72);
  border-right-color: rgba(13, 122, 130, .50);
  filter            : drop-shadow(0 0 calc(var(--gw)*.05) rgba(13,122,130,.38));
  animation         : wfgOrbit_{{$gid}} 5.8s cubic-bezier(.37,.0,.63,1) infinite;
}
@keyframes wfgOrbit_{{$gid}} {
  0%   { transform: rotateX(-88deg) rotateZ(6deg);  opacity:.07; }
  15%  { transform: rotateX(-55deg) rotateZ(3deg);  opacity:.55; }
  35%  { transform: rotateX(-18deg) rotateZ(1deg);  opacity:.95; }
  50%  { transform: rotateX(  8deg) rotateZ(-1deg); opacity:1.0; }
  65%  { transform: rotateX( 32deg) rotateZ(-3deg); opacity:.84; }
  80%  { transform: rotateX( 60deg) rotateZ(-5deg); opacity:.38; }
  100% { transform: rotateX( 92deg) rotateZ(-8deg); opacity:.05; }
}

/* ── Second orbit ring (depth layer) ────────────────── */
#{{$gid}} .wfg-orbit-ring2 {
  position          : absolute;
  width             : calc(var(--gw) * 1.30);
  height            : calc(var(--gw) * 1.30);
  top               : calc(var(--gw) * -.15);
  left              : calc(var(--gw) * -.15);
  border-radius     : 50%;
  border            : calc(max(1.5px, var(--gw)*.020)) solid transparent;
  border-top-color  : rgba(13,122,130,.60);
  border-right-color: rgba(13,122,130,.36);
  animation         : wfgOrbit2_{{$gid}} 5.8s cubic-bezier(.37,.0,.63,1) -.95s infinite;
  pointer-events    : none;
}
@keyframes wfgOrbit2_{{$gid}} {
  0%   { transform: rotateX(-88deg) rotateZ(-5deg); opacity:.05; }
  15%  { transform: rotateX(-52deg) rotateZ(-3deg); opacity:.44; }
  35%  { transform: rotateX(-14deg) rotateZ(-1deg); opacity:.80; }
  50%  { transform: rotateX( 10deg) rotateZ( 1deg); opacity:.88; }
  65%  { transform: rotateX( 35deg) rotateZ( 3deg); opacity:.66; }
  80%  { transform: rotateX( 65deg) rotateZ( 5deg); opacity:.28; }
  100% { transform: rotateX( 95deg) rotateZ( 7deg); opacity:.04; }
}

/* ── Company text ─────────────────────────────────────── */
#{{$gid}} .wfg-text {
  text-align : center;
  direction  : rtl;
  line-height: 1.25;
  @if($hasText) display:flex; flex-direction:column; align-items:center; @else display:none; @endif
}
#{{$gid}} .wfg-name {
  font-family   : 'Tajawal', 'Segoe UI', Arial, sans-serif;
  font-size     : {{ $nm_fs }}px;
  font-weight   : 900;
  letter-spacing: .3px;
  white-space   : nowrap;
  @if($dark)
    /* Exact charcoal from the official logo */
    color : #3D4044;
  @else
    /* On dark backgrounds: clean white with subtle teal glow */
    color : #FFFFFF;
    filter: drop-shadow(0 1px 4px rgba(27,155,164,.30));
  @endif
  animation : wfgNameIn_{{$gid}} .9s cubic-bezier(.34,1.56,.64,1) .22s both;
}
#{{$gid}} .wfg-tagline {
  font-family : 'Tajawal', 'Segoe UI', Arial, sans-serif;
  font-size   : {{ $tg_fs }}px;
  font-weight : 500;
  letter-spacing: .3px;
  margin-top  : 1px;
  @if($dark)
    /* Exact gray from the official logo */
    color : #888888;
  @else
    color : rgba(200, 225, 228, .85);
  @endif
  animation : wfgNameIn_{{$gid}} .9s cubic-bezier(.34,1.56,.64,1) .38s both;
}
@keyframes wfgNameIn_{{$gid}} {
  from { opacity:0; transform:translateY(5px); }
  to   { opacity:1; transform:none; }
}
</style>

{{-- ══════════════ DOM ══════════════ --}}
<div id="{{$gid}}">

  <div class="wfg-scene">

    {{-- SVG dot-globe (static tiles, matches official logo) --}}
    <svg class="wfg-dots" viewBox="0 0 100 100"
         xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
      <defs>
        <clipPath id="{{$gid}}_clip">
          <circle cx="{{$sCx}}" cy="{{$sCy}}" r="{{$sR + 0.5}}"/>
        </clipPath>
      </defs>
      <g clip-path="url(#{{$gid}}_clip)">
        @foreach($dots as $d)
        <rect x="{{$d['x']}}" y="{{$d['y']}}"
              width="{{$dW}}" height="{{$dH}}" rx="{{$dRx}}"
              fill="{{$d['c']}}"/>
        @endforeach
      </g>
    </svg>

    {{-- Primary orbit arc: back → over top → front --}}
    <div class="wfg-orbit-wrap">
      <div class="wfg-orbit-ring"></div>
    </div>

    {{-- Depth-layer orbit ring (−0.95 s phase offset) --}}
    <div class="wfg-orbit-ring2"></div>

  </div>

  {{-- Company name --}}
  <div class="wfg-text">
    <div class="wfg-name">وفرة الخليجية</div>
    <div class="wfg-tagline">للخدمات المالية</div>
  </div>

</div>
