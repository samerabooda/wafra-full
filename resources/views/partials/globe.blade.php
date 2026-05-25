{{--
  ╔══════════════════════════════════════════════════════════╗
  ║  Wafra Gulf — 3D Animated Globe Logo Component  v3.0     ║
  ║  True CSS 3D with orbital rings from back-to-front       ║
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
   WFG-3D Globe — scoped to #{{$gid}}
   ═══════════════════════════════════════════════════════ */
#{{$gid}} {
  --gw : {{ $gw }}px;
  display       : inline-flex;
  flex-direction: column;
  align-items   : center;
  gap           : {{ $gap }}px;
  animation     : wfgIn_{{$gid}} .8s cubic-bezier(.34,1.56,.64,1) both;
}
@keyframes wfgIn_{{$gid}}{
  from{ opacity:0; transform:scale(.6) translateY(10px); }
  to  { opacity:1; transform:none; }
}

/* ── Scene (perspective parent) ───────────────────── */
#{{$gid}} .wfg-scene {
  width    : var(--gw);
  height   : var(--gw);
  position : relative;
  /* Extra room for orbit rings that extend beyond sphere */
  overflow : visible;
}

/* ── Globe wrapper: rotates on Y axis ─────────────── */
#{{$gid}} .wfg-globe {
  position         : absolute;
  inset            : 0;
  transform-style  : preserve-3d;
  perspective      : calc(var(--gw) * 4.5);
  animation        : wfgSpinY_{{$gid}} 14s linear infinite;
}
@keyframes wfgSpinY_{{$gid}} {
  from { transform: rotateY(0deg);    }
  to   { transform: rotateY(-360deg); }
}

/* ── Sphere ───────────────────────────────────────── */
#{{$gid}} .wfg-sphere {
  position     : absolute;
  inset        : 0;
  border-radius: 50%;
  background   : radial-gradient(circle at 32% 27%,
    #88E4F8 0%,
    #3BBAD9 18%,
    #1878AB 40%,
    #0D4576 68%,
    #071E40 100%
  );
  box-shadow   :
    inset calc(var(--gw)*-.09) calc(var(--gw)*-.09) calc(var(--gw)*.22) rgba(0,0,0,.55),
    inset calc(var(--gw)*.03)  calc(var(--gw)*.03)  calc(var(--gw)*.14) rgba(160,240,255,.18),
    0 0 calc(var(--gw)*.38) rgba(46,134,171,.28);
  overflow     : hidden;
}
/* Specular highlight */
#{{$gid}} .wfg-sphere::before {
  content      : '';
  position     : absolute;
  width: 38%; height: 26%;
  top: 10%; left: 11%;
  background   : radial-gradient(ellipse at center, rgba(255,255,255,.32), transparent 70%);
  border-radius: 50%;
  transform    : rotate(-22deg);
}
/* Secondary rim glow */
#{{$gid}} .wfg-sphere::after {
  content      : '';
  position     : absolute;
  inset        : 0;
  border-radius: 50%;
  box-shadow   : inset 0 0 calc(var(--gw)*.1) rgba(100,220,255,.12);
}

/* ── Latitude bands (inside spinning globe) ───────── */
#{{$gid}} .wfg-band {
  position       : absolute;
  border-radius  : 50%;
  border         : calc(max(1.5px, var(--gw)*.022)) solid transparent;
  transform-style: preserve-3d;
  pointer-events : none;
}

/* Band A — near equator */
#{{$gid}} .wfg-ba {
  inset             : -4%;
  border-top-color  : rgba(120,220,250,.72);
  border-bottom-color: rgba(120,220,250,.22);
  transform         : rotateX(76deg);
}
/* Band B — mid latitude */
#{{$gid}} .wfg-bb {
  inset             : -9%;
  border-top-color  : rgba(90,195,235,.78);
  border-bottom-color: rgba(90,195,235,.18);
  border-left-color : rgba(90,195,235,.35);
  border-right-color: rgba(90,195,235,.35);
  transform         : rotateX(58deg);
}
/* Band C — high latitude */
#{{$gid}} .wfg-bc {
  inset             : -14%;
  border-top-color  : rgba(65,170,218,.82);
  border-bottom-color: rgba(65,170,218,.15);
  border-left-color : rgba(65,170,218,.5);
  border-right-color: rgba(65,170,218,.5);
  transform         : rotateX(40deg);
}
/* Band D — lower latitude */
#{{$gid}} .wfg-bd {
  inset             : -6%;
  border-top-color  : rgba(100,210,240,.55);
  border-bottom-color: rgba(100,210,240,.55);
  border-left-color : rgba(100,210,240,.18);
  border-right-color: rgba(100,210,240,.18);
  transform         : rotateX(22deg);
}
/* Band E — vertical meridian */
#{{$gid}} .wfg-be {
  inset             : -4%;
  border-left-color : rgba(110,215,245,.48);
  border-right-color: rgba(110,215,245,.15);
  transform         : rotateY(82deg);
}

/* ══════════════════════════════════════════════════
   TOP ORBIT RING — independent 3D arc
   Comes from behind → passes over the top → goes front
   ══════════════════════════════════════════════════ */
#{{$gid}} .wfg-orbit-wrap {
  position        : absolute;
  /* Size: 148% of globe so arc visibly extends beyond sphere */
  width           : calc(var(--gw) * 1.48);
  height          : calc(var(--gw) * 1.48);
  top             : calc(var(--gw) * -.24);
  left            : calc(var(--gw) * -.24);
  transform-style : preserve-3d;
  perspective     : calc(var(--gw) * 5);
  pointer-events  : none;
}
#{{$gid}} .wfg-orbit-ring {
  position        : absolute;
  inset           : 0;
  border-radius   : 50%;
  border          : calc(max(2px, var(--gw)*.026)) solid transparent;
  /* Only top-half arc visible — creates C-shape that orbits */
  border-top-color  : rgba(185,245,255,.95);
  border-left-color : rgba(145,225,248,.62);
  border-right-color: rgba(145,225,248,.45);
  /* Glow effect */
  filter          : drop-shadow(0 0 calc(var(--gw)*.07) rgba(140,225,255,.55));
  animation       : wfgOrbit_{{$gid}} 5.8s cubic-bezier(.37,.0,.63,1) infinite;
}
@keyframes wfgOrbit_{{$gid}} {
  0%   { transform: rotateX(-88deg) rotateZ(8deg);  opacity: .08; }
  15%  { transform: rotateX(-55deg) rotateZ(4deg);  opacity: .5;  }
  35%  { transform: rotateX(-18deg) rotateZ(1deg);  opacity: .92; }
  50%  { transform: rotateX(8deg)   rotateZ(-1deg); opacity: 1;   }
  65%  { transform: rotateX(32deg)  rotateZ(-3deg); opacity: .78; }
  80%  { transform: rotateX(60deg)  rotateZ(-6deg); opacity: .35; }
  100% { transform: rotateX(92deg)  rotateZ(-9deg); opacity: .05; }
}

/* ── Second orbit ring — offset for depth layering ── */
#{{$gid}} .wfg-orbit-ring2 {
  position        : absolute;
  width           : calc(var(--gw) * 1.28);
  height          : calc(var(--gw) * 1.28);
  top             : calc(var(--gw) * -.14);
  left            : calc(var(--gw) * -.14);
  border-radius   : 50%;
  border          : calc(max(1.5px, var(--gw)*.018)) solid transparent;
  border-top-color  : rgba(140,220,245,.6);
  border-right-color: rgba(140,220,245,.35);
  filter          : drop-shadow(0 0 calc(var(--gw)*.05) rgba(100,200,240,.35));
  animation       : wfgOrbit2_{{$gid}} 5.8s cubic-bezier(.37,.0,.63,1) -.9s infinite;
  pointer-events  : none;
}
@keyframes wfgOrbit2_{{$gid}} {
  0%   { transform: rotateX(-88deg) rotateZ(-5deg); opacity: .06; }
  15%  { transform: rotateX(-52deg) rotateZ(-3deg); opacity: .42; }
  35%  { transform: rotateX(-14deg) rotateZ(-1deg); opacity: .78; }
  50%  { transform: rotateX(10deg)  rotateZ(1deg);  opacity: .88; }
  65%  { transform: rotateX(35deg)  rotateZ(3deg);  opacity: .65; }
  80%  { transform: rotateX(65deg)  rotateZ(5deg);  opacity: .28; }
  100% { transform: rotateX(95deg)  rotateZ(7deg);  opacity: .04; }
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
  from{ opacity:0; transform:translateY(6px); }
  to  { opacity:1; transform:none; }
}
</style>

{{-- ══════════════ DOM ══════════════ --}}
<div id="{{$gid}}">

  {{-- 3D Scene --}}
  <div class="wfg-scene">

    {{-- Globe: spins on Y axis --}}
    <div class="wfg-globe">
      <div class="wfg-sphere"></div>
      <div class="wfg-band wfg-ba"></div>
      <div class="wfg-band wfg-bb"></div>
      <div class="wfg-band wfg-bc"></div>
      <div class="wfg-band wfg-bd"></div>
      <div class="wfg-band wfg-be"></div>
    </div>

    {{-- Top orbit ring (back → front animation, independent of globe spin) --}}
    <div class="wfg-orbit-wrap">
      <div class="wfg-orbit-ring"></div>
    </div>
    {{-- Second orbit ring (offset phase) --}}
    <div class="wfg-orbit-ring2"></div>

  </div>

  {{-- Company name --}}
  <div class="wfg-text">
    <div class="wfg-name">وفرة الخليجية</div>
    <div class="wfg-tagline">للخدمات المالية</div>
  </div>

</div>
