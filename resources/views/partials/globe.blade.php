{{--
  ╔══════════════════════════════════════════════════════════╗
  ║  Wafra Gulf — 3D Animated Globe Logo Component  v6.0     ║
  ║  Exact logo colors: steel-blue wireframe globe           ║
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
  $nm_fs   = max(11, (int)round($gw * 0.21));
  $tg_fs   = max(8,  (int)round($gw * 0.125));
  $gap     = max(5,  (int)round($gw * 0.09));
@endphp

<style>
/* ═══════════════════════════════════════════════════════
   WFG Globe v6 — exact logo colors — scoped to #{{$gid}}
   Steel-blue wireframe palette extracted from official logo
   ═══════════════════════════════════════════════════════ */

/* ── Logo palette (steel-blue family) ───────────────────
   highlight : #B2D0E6   (light steel blue)
   mid-light : #6A9EC4   (cornflower steel)
   mid       : #4A82B0   (main logo blue)
   deep      : #2B5F8E   (shadow blue)
   darkest   : #17395E   (deep shadow)
   arc       : #2A5C8C   (solid, matches logo arc)
   ─────────────────────────────────────────────────────── */

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

/* ── Scene ──────────────────────────────────────────── */
#{{$gid}} .wfg-scene {
  width    : var(--gw);
  height   : var(--gw);
  position : relative;
  overflow : visible;
}

/* ── Globe wrapper: spins on Y axis ─────────────────── */
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

/* ── Sphere — steel-blue translucent wireframe ──────── */
#{{$gid}} .wfg-sphere {
  position     : absolute;
  inset        : 0;
  border-radius: 50%;
  /*
    Steel-blue radial gradient matching the official logo sphere:
    upper-left = lightest (reflected light), lower-right = deepest
  */
  background   : radial-gradient(circle at 33% 27%,
    rgba(178, 208, 230, .62)  0%,
    rgba(106, 158, 196, .50) 25%,
    rgba( 74, 130, 176, .44) 50%,
    rgba( 43,  95, 142, .40) 72%,
    rgba( 23,  57,  94, .38) 100%
  );
  /* Crisp outer circle — the defining circle of the logo globe */
  border       : calc(max(1.5px, var(--gw)*.027)) solid rgba(74,130,176,.88);
  box-shadow   :
    /* inner light (top-left) */
    inset  calc(var(--gw)* .065)  calc(var(--gw)* .065)  calc(var(--gw)*.16) rgba(178,215,240,.24),
    /* inner shadow (bottom-right) */
    inset  calc(var(--gw)*-.055)  calc(var(--gw)*-.055)  calc(var(--gw)*.14) rgba(20, 50, 90,.28),
    /* outer glow */
    0 0    calc(var(--gw)* .18)   rgba(74,130,176,.14);
  overflow     : hidden;
}
/* Specular highlight — top-left glint */
#{{$gid}} .wfg-sphere::before {
  content      : '';
  position     : absolute;
  width:36%; height:24%;
  top:9%; left:11%;
  background   : radial-gradient(ellipse,
    rgba(220,238,252,.48), transparent 72%
  );
  border-radius: 50%;
  transform    : rotate(-18deg);
}

/* ── Latitude / Meridian grid bands ─────────────────── */
#{{$gid}} .wfg-band {
  position       : absolute;
  border-radius  : 50%;
  border         : calc(max(1px, var(--gw)*.016)) solid transparent;
  transform-style: preserve-3d;
  pointer-events : none;
}

/* ---- Latitude lines -------------------------------- */

/* Equator */
#{{$gid}} .wfg-lat1 {
  inset              : -2%;
  border-top-color   : rgba(90,148,190,.88);
  border-bottom-color: rgba(90,148,190,.88);
  border-left-color  : rgba(74,130,175,.60);
  border-right-color : rgba(74,130,175,.60);
  transform          : rotateX(90deg);
}
/* ~25° N/S */
#{{$gid}} .wfg-lat2 {
  inset              : -4%;
  border-top-color   : rgba(82,138,182,.78);
  border-bottom-color: rgba(55, 98,148,.50);
  border-left-color  : rgba(70,120,168,.60);
  border-right-color : rgba(70,120,168,.60);
  transform          : rotateX(68deg);
}
/* ~50° N/S */
#{{$gid}} .wfg-lat3 {
  inset              : -7%;
  border-top-color   : rgba(74,128,175,.70);
  border-bottom-color: rgba(46, 88,138,.40);
  border-left-color  : rgba(62,110,158,.52);
  border-right-color : rgba(62,110,158,.52);
  transform          : rotateX(46deg);
}
/* ~70° N/S */
#{{$gid}} .wfg-lat4 {
  inset              : -11%;
  border-top-color   : rgba(66,118,165,.58);
  border-bottom-color: rgba(36, 76,126,.28);
  border-left-color  : rgba(52, 98,148,.40);
  border-right-color : rgba(52, 98,148,.40);
  transform          : rotateX(26deg);
}

/* ---- Meridian lines (3 × 60° spacing) -------------- */

#{{$gid}} .wfg-mer1 {
  inset              : -2%;
  border-left-color  : rgba(86,144,188,.72);
  border-right-color : rgba(56,100,152,.46);
  border-top-color   : rgba(72,124,170,.54);
  border-bottom-color: rgba(72,124,170,.54);
  transform          : rotateY(0deg);
}
#{{$gid}} .wfg-mer2 {
  inset              : -2%;
  border-left-color  : rgba(86,144,188,.68);
  border-right-color : rgba(56,100,152,.42);
  border-top-color   : rgba(72,124,170,.50);
  border-bottom-color: rgba(72,124,170,.50);
  transform          : rotateY(60deg);
}
#{{$gid}} .wfg-mer3 {
  inset              : -2%;
  border-left-color  : rgba(86,144,188,.62);
  border-right-color : rgba(56,100,152,.38);
  border-top-color   : rgba(72,124,170,.46);
  border-bottom-color: rgba(72,124,170,.46);
  transform          : rotateY(120deg);
}

/* ══════════════════════════════════════════════════════
   TOP ORBIT ARC — steel-blue matching the logo's arc
   Animates from behind-globe → over-top → behind-globe
   ══════════════════════════════════════════════════════ */
#{{$gid}} .wfg-orbit-wrap {
  position        : absolute;
  width           : calc(var(--gw) * 1.56);
  height          : calc(var(--gw) * 1.56);
  top             : calc(var(--gw) * -.28);
  left            : calc(var(--gw) * -.28);
  transform-style : preserve-3d;
  perspective     : calc(var(--gw) * 5);
  pointer-events  : none;
}
#{{$gid}} .wfg-orbit-ring {
  position          : absolute;
  inset             : 0;
  border-radius     : 50%;
  /* Solid steel-blue arc matching the logo's bold arc */
  border            : calc(max(2.5px, var(--gw)*.033)) solid transparent;
  border-top-color  : rgba(42, 92,148,1.00);
  border-left-color : rgba(58,108,162,.70);
  border-right-color: rgba(58,108,162,.52);
  filter            : drop-shadow(0 0 calc(var(--gw)*.06) rgba(74,130,176,.45));
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

/* ── Second orbit ring — depth layer ────────────────── */
#{{$gid}} .wfg-orbit-ring2 {
  position          : absolute;
  width             : calc(var(--gw) * 1.32);
  height            : calc(var(--gw) * 1.32);
  top               : calc(var(--gw) * -.16);
  left              : calc(var(--gw) * -.16);
  border-radius     : 50%;
  border            : calc(max(1.5px, var(--gw)*.020)) solid transparent;
  border-top-color  : rgba(68,118,166,.60);
  border-right-color: rgba(52, 96,148,.36);
  filter            : drop-shadow(0 0 calc(var(--gw)*.04) rgba(74,130,176,.26));
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

/* ── Company text ─────────────────────────────────── */
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
    /* Exact logo dark-navy text color */
    color : #152A4A;
  @else
    /* Steel-blue gradient for dark-background contexts */
    background: linear-gradient(135deg, #B2D0E8, #6A9EC4 42%, #3A7AB0);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    filter    : drop-shadow(0 1px 5px rgba(74,130,176,.38));
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
    /* Exact logo tagline color */
    color : #2E5070;
  @else
    color : rgba(162, 200, 228, .78);
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

    {{-- Globe: spins on Y axis --}}
    <div class="wfg-globe">
      <div class="wfg-sphere"></div>

      {{-- 4 latitude lines --}}
      <div class="wfg-band wfg-lat1"></div>
      <div class="wfg-band wfg-lat2"></div>
      <div class="wfg-band wfg-lat3"></div>
      <div class="wfg-band wfg-lat4"></div>

      {{-- 3 meridian lines at 60° spacing --}}
      <div class="wfg-band wfg-mer1"></div>
      <div class="wfg-band wfg-mer2"></div>
      <div class="wfg-band wfg-mer3"></div>
    </div>

    {{-- Primary orbit arc (back → top → front) --}}
    <div class="wfg-orbit-wrap">
      <div class="wfg-orbit-ring"></div>
    </div>

    {{-- Secondary orbit ring (depth layer, −0.9s offset) --}}
    <div class="wfg-orbit-ring2"></div>

  </div>

  {{-- Company name --}}
  <div class="wfg-text">
    <div class="wfg-name">وفرة الخليجية</div>
    <div class="wfg-tagline">للخدمات المالية</div>
  </div>

</div>
