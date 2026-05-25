{{--
  ╔══════════════════════════════════════════════════════════╗
  ║  Wafra Gulf — 3D Animated Globe Logo Component  v5.0     ║
  ║  Wireframe grid globe matching the official logo         ║
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
  $gid     = isset($gid) ? preg_replace('/[^a-z0-9]/i','_',$gid) : ('wfg_'.rand(1000,9999));
  $sz      = $size     ?? 'md';
  $gw      = match($sz){ 'xs'=>42,'sm'=>58,'lg'=>128,'xl'=>172, default=>96 };
  $hasText = $showText ?? true;
  $dark    = $darkText ?? false;
  /* derived values */
  $nm_fs   = max(11, (int)round($gw * 0.21));
  $tg_fs   = max(8,  (int)round($gw * 0.125));
  $gap     = max(5,  (int)round($gw * 0.09));
@endphp

<style>
/* ═══════════════════════════════════════════════════════
   WFG Globe v5 — wireframe style — scoped to #{{$gid}}
   ═══════════════════════════════════════════════════════ */
#{{$gid}} {
  --gw : {{ $gw }}px;
  display       : inline-flex;
  flex-direction: column;
  align-items   : center;
  gap           : {{ $gap }}px;
  animation     : wfgIn_{{$gid}} .8s cubic-bezier(.34,1.56,.64,1) both;
}
@keyframes wfgIn_{{$gid}} {
  from { opacity:0; transform:scale(.6) translateY(10px); }
  to   { opacity:1; transform:none; }
}

/* ── Scene ─────────────────────────────────────────────── */
#{{$gid}} .wfg-scene {
  width    : var(--gw);
  height   : var(--gw);
  position : relative;
  overflow : visible;
}

/* ── Globe wrapper: rotates on Y axis ──────────────────── */
#{{$gid}} .wfg-globe {
  position        : absolute;
  inset           : 0;
  transform-style : preserve-3d;
  perspective     : calc(var(--gw) * 4.5);
  animation       : wfgSpinY_{{$gid}} 16s linear infinite;
}
@keyframes wfgSpinY_{{$gid}} {
  from { transform: rotateY(0deg);    }
  to   { transform: rotateY(-360deg); }
}

/* ── Sphere — wireframe look matching official logo ─────── */
#{{$gid}} .wfg-sphere {
  position     : absolute;
  inset        : 0;
  border-radius: 50%;
  /* Light translucent fill — grid lines do the visual work */
  background   : radial-gradient(circle at 34% 28%,
    rgba(150,220,250, .50) 0%,
    rgba( 65,158,215, .38) 28%,
    rgba( 32,112,178, .33) 58%,
    rgba( 12, 62,132, .30) 100%
  );
  /* Clear circular border — key part of the logo look */
  border       : calc(max(1.5px, var(--gw)*.028)) solid rgba(48,138,202,.85);
  box-shadow   :
    inset  calc(var(--gw)* .07)  calc(var(--gw)* .07)  calc(var(--gw)*.18) rgba(155,225,255,.20),
    inset  calc(var(--gw)*-.06)  calc(var(--gw)*-.06)  calc(var(--gw)*.15) rgba(15, 68,138,.22),
    0 0    calc(var(--gw)* .20)  rgba(40,120,192,.16);
  overflow     : hidden;
}
/* Specular highlight (top-left reflection) */
#{{$gid}} .wfg-sphere::before {
  content      : '';
  position     : absolute;
  width:38%; height:25%;
  top:9%; left:12%;
  background   : radial-gradient(ellipse, rgba(210,245,255,.42), transparent 72%);
  border-radius: 50%;
  transform    : rotate(-18deg);
}

/* ── Grid bands (latitude + meridian lines) ─────────────── */
#{{$gid}} .wfg-band {
  position       : absolute;
  border-radius  : 50%;
  border         : calc(max(1px, var(--gw)*.017)) solid transparent;
  transform-style: preserve-3d;
  pointer-events : none;
}

/* ---- Latitude bands (rotateX ≈ horizontal ellipses) ---- */

/* Equator — perfectly horizontal ring */
#{{$gid}} .wfg-lat1 {
  inset              : -2%;
  border-top-color   : rgba(62,152,215,.90);
  border-bottom-color: rgba(62,152,215,.90);
  border-left-color  : rgba(62,152,215,.62);
  border-right-color : rgba(62,152,215,.62);
  transform          : rotateX(90deg);
}
/* ~25° N/S */
#{{$gid}} .wfg-lat2 {
  inset              : -4%;
  border-top-color   : rgba(55,144,208,.80);
  border-bottom-color: rgba(34,104,168,.52);
  border-left-color  : rgba(44,124,190,.62);
  border-right-color : rgba(44,124,190,.62);
  transform          : rotateX(68deg);
}
/* ~50° N/S */
#{{$gid}} .wfg-lat3 {
  inset              : -7%;
  border-top-color   : rgba(48,136,200,.72);
  border-bottom-color: rgba(26, 94,158,.42);
  border-left-color  : rgba(38,116,180,.54);
  border-right-color : rgba(38,116,180,.54);
  transform          : rotateX(46deg);
}
/* ~70° N/S */
#{{$gid}} .wfg-lat4 {
  inset              : -11%;
  border-top-color   : rgba(42,128,194,.60);
  border-bottom-color: rgba(20, 82,146,.30);
  border-left-color  : rgba(32,106,170,.42);
  border-right-color : rgba(32,106,170,.42);
  transform          : rotateX(26deg);
}

/* ---- Meridian bands (rotateY ≈ vertical ellipses) ------- */
/* 3 meridians at 60° spacing — as globe spins they all appear */

#{{$gid}} .wfg-mer1 {
  inset              : -2%;
  border-left-color  : rgba(58,148,212,.75);
  border-right-color : rgba(36,108,172,.48);
  border-top-color   : rgba(48,130,194,.56);
  border-bottom-color: rgba(48,130,194,.56);
  transform          : rotateY(0deg);
}
#{{$gid}} .wfg-mer2 {
  inset              : -2%;
  border-left-color  : rgba(58,148,212,.70);
  border-right-color : rgba(36,108,172,.44);
  border-top-color   : rgba(48,130,194,.52);
  border-bottom-color: rgba(48,130,194,.52);
  transform          : rotateY(60deg);
}
#{{$gid}} .wfg-mer3 {
  inset              : -2%;
  border-left-color  : rgba(58,148,212,.65);
  border-right-color : rgba(36,108,172,.40);
  border-top-color   : rgba(48,130,194,.48);
  border-bottom-color: rgba(48,130,194,.48);
  transform          : rotateY(120deg);
}

/* ══════════════════════════════════════════════════════════
   TOP ORBIT ARC — matches the prominent arc in official logo
   Animates from behind-globe → over-top → behind-globe
   ══════════════════════════════════════════════════════════ */
#{{$gid}} .wfg-orbit-wrap {
  position        : absolute;
  /* 155% of globe so arc clearly extends beyond sphere edge */
  width           : calc(var(--gw) * 1.55);
  height          : calc(var(--gw) * 1.55);
  top             : calc(var(--gw) * -.275);
  left            : calc(var(--gw) * -.275);
  transform-style : preserve-3d;
  perspective     : calc(var(--gw) * 5);
  pointer-events  : none;
}
#{{$gid}} .wfg-orbit-ring {
  position          : absolute;
  inset             : 0;
  border-radius     : 50%;
  /* Thicker border matching the logo's bold arc */
  border            : calc(max(2.5px, var(--gw)*.032)) solid transparent;
  /* Top-arc solid, sides fade — creates C-shape matching logo */
  border-top-color  : rgba(32,112,180,1.00);
  border-left-color : rgba(44,128,196,.68);
  border-right-color: rgba(44,128,196,.50);
  filter            : drop-shadow(0 0 calc(var(--gw)*.065) rgba(55,148,215,.50));
  animation         : wfgOrbit_{{$gid}} 5.8s cubic-bezier(.37,.0,.63,1) infinite;
}
@keyframes wfgOrbit_{{$gid}} {
  0%   { transform: rotateX(-88deg) rotateZ(8deg);  opacity: .07; }
  15%  { transform: rotateX(-55deg) rotateZ(4deg);  opacity: .55; }
  35%  { transform: rotateX(-18deg) rotateZ(1deg);  opacity: .95; }
  50%  { transform: rotateX(  8deg) rotateZ(-1deg); opacity: 1.0; }
  65%  { transform: rotateX( 32deg) rotateZ(-3deg); opacity: .82; }
  80%  { transform: rotateX( 60deg) rotateZ(-6deg); opacity: .36; }
  100% { transform: rotateX( 92deg) rotateZ(-9deg); opacity: .05; }
}

/* ── Second orbit ring — depth layer, offset phase ─────── */
#{{$gid}} .wfg-orbit-ring2 {
  position          : absolute;
  width             : calc(var(--gw) * 1.32);
  height            : calc(var(--gw) * 1.32);
  top               : calc(var(--gw) * -.16);
  left              : calc(var(--gw) * -.16);
  border-radius     : 50%;
  border            : calc(max(1.5px, var(--gw)*.020)) solid transparent;
  border-top-color  : rgba(52,142,205,.62);
  border-right-color: rgba(42,126,192,.38);
  filter            : drop-shadow(0 0 calc(var(--gw)*.04) rgba(48,138,205,.28));
  animation         : wfgOrbit2_{{$gid}} 5.8s cubic-bezier(.37,.0,.63,1) -.9s infinite;
  pointer-events    : none;
}
@keyframes wfgOrbit2_{{$gid}} {
  0%   { transform: rotateX(-88deg) rotateZ(-5deg); opacity: .05; }
  15%  { transform: rotateX(-52deg) rotateZ(-3deg); opacity: .44; }
  35%  { transform: rotateX(-14deg) rotateZ(-1deg); opacity: .80; }
  50%  { transform: rotateX( 10deg) rotateZ( 1deg); opacity: .90; }
  65%  { transform: rotateX( 35deg) rotateZ( 3deg); opacity: .66; }
  80%  { transform: rotateX( 65deg) rotateZ( 5deg); opacity: .28; }
  100% { transform: rotateX( 95deg) rotateZ( 7deg); opacity: .04; }
}

/* ── Company text ─────────────────────────────────────────── */
#{{$gid}} .wfg-text {
  text-align : center;
  direction  : rtl;
  line-height: 1.2;
  @if($hasText) display:flex; flex-direction:column; align-items:center; @else display:none; @endif
}
#{{$gid}} .wfg-name {
  font-family   : 'Tajawal', 'Segoe UI', Arial, sans-serif;
  font-size     : {{ $nm_fs }}px;
  font-weight   : 900;
  letter-spacing: .4px;
  white-space   : nowrap;
  @if($dark)
    color     : #0e2d5c;
  @else
    background: linear-gradient(135deg,#9EEEFF,#4AC8E8 40%,#2296BC);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    filter    : drop-shadow(0 1px 6px rgba(80,200,240,.35));
  @endif
  animation     : wfgNameIn_{{$gid}} 1s cubic-bezier(.34,1.56,.64,1) .25s both;
}
#{{$gid}} .wfg-tagline {
  font-family : 'Tajawal', 'Segoe UI', Arial, sans-serif;
  font-size   : {{ $tg_fs }}px;
  font-weight : 500;
  letter-spacing: .3px;
  margin-top  : 1px;
  @if($dark)
    color     : #3a6a8a;
  @else
    color     : rgba(140,210,240,.72);
  @endif
  animation   : wfgNameIn_{{$gid}} 1s cubic-bezier(.34,1.56,.64,1) .4s both;
}
@keyframes wfgNameIn_{{$gid}} {
  from { opacity:0; transform:translateY(6px); }
  to   { opacity:1; transform:none; }
}
</style>

{{-- ══════════════ DOM ══════════════ --}}
<div id="{{$gid}}">

  {{-- 3D Scene --}}
  <div class="wfg-scene">

    {{-- Globe: spins on Y axis (all grid lines spin with it) --}}
    <div class="wfg-globe">
      <div class="wfg-sphere"></div>

      {{-- Latitude lines (4 horizontal ellipses) --}}
      <div class="wfg-band wfg-lat1"></div>
      <div class="wfg-band wfg-lat2"></div>
      <div class="wfg-band wfg-lat3"></div>
      <div class="wfg-band wfg-lat4"></div>

      {{-- Meridian lines (3 vertical ellipses at 60° spacing) --}}
      <div class="wfg-band wfg-mer1"></div>
      <div class="wfg-band wfg-mer2"></div>
      <div class="wfg-band wfg-mer3"></div>
    </div>

    {{-- Primary orbit arc — back → top → front (matches logo arc) --}}
    <div class="wfg-orbit-wrap">
      <div class="wfg-orbit-ring"></div>
    </div>

    {{-- Secondary orbit ring — depth layer, offset phase --}}
    <div class="wfg-orbit-ring2"></div>

  </div>

  {{-- Company name --}}
  <div class="wfg-text">
    <div class="wfg-name">وفرة الخليجية</div>
    <div class="wfg-tagline">للخدمات المالية</div>
  </div>

</div>
