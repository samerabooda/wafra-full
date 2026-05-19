{{--
  ╔══════════════════════════════════════════════════════════╗
  ║  Wafra Gulf — Animated 3-D Globe Logo Component          ║
  ║  Usage:                                                  ║
  ║    @include('partials.globe', [                          ║
  ║      'size'     => 'xs|sm|md|lg|xl',  (default: 'md')   ║
  ║      'showText' => true|false,         (default: true)   ║
  ║      'gid'      => 'myUniqueId',       (optional)        ║
  ║      'style'    => 'extra CSS',        (optional)        ║
  ║    ])                                                     ║
  ╚══════════════════════════════════════════════════════════╝
--}}
@php
  $gid     = $gid ?? ('wfg'.substr(md5(uniqid()),0,8));
  $sz      = $size ?? 'md';
  $w       = match($sz) { 'xs'=>36, 'sm'=>52, 'lg'=>150, 'xl'=>180, default=>100 };
  $hasText = $showText ?? true;
  $viewH   = $hasText ? 200 : 158;
  $h       = (int)round($w * ($viewH / 160));
@endphp
<svg id="{{$gid}}" viewBox="0 0 160 {{$viewH}}" width="{{$w}}" height="{{$h}}"
  xmlns="http://www.w3.org/2000/svg"
  style="display:block;overflow:visible;{{$style??''}}">
<defs>
  {{-- Sphere base gradient: bright teal top-left → dark navy bottom-right --}}
  <radialGradient id="sg{{$gid}}" cx="29%" cy="24%" r="77%">
    <stop offset="0%"   stop-color="#5ACDE6"/>
    <stop offset="35%"  stop-color="#1782AE"/>
    <stop offset="100%" stop-color="#092844"/>
  </radialGradient>
  {{-- Depth vignette overlay --}}
  <radialGradient id="sv{{$gid}}" cx="73%" cy="74%" r="60%">
    <stop offset="10%" stop-color="#000" stop-opacity="0"/>
    <stop offset="100%" stop-color="#000" stop-opacity="0.44"/>
  </radialGradient>
  {{-- Clip mask to sphere circle --}}
  <clipPath id="sc{{$gid}}">
    <circle cx="80" cy="79" r="71"/>
  </clipPath>
</defs>

{{-- ── Sphere base ─────────────────────────────────────── --}}
<circle cx="80" cy="79" r="71" fill="url(#sg{{$gid}})"/>

{{-- ── Animated surface (dots clipped to sphere) ──────── --}}
<g clip-path="url(#sc{{$gid}})">
  {{-- Dots layer — JS fills this with circles --}}
  <g id="dl{{$gid}}" style="animation:wfs{{$gid}} 2s cubic-bezier(.28,.48,.4,.96) both"></g>

  {{-- Depth vignette (bottom-right shadow) --}}
  <circle cx="80" cy="79" r="71" fill="url(#sv{{$gid}})"/>

  {{-- Specular highlight (upper-left glow) --}}
  <ellipse cx="50" cy="49" rx="29" ry="21"
    fill="white" fill-opacity="0.14"
    transform="rotate(-18 50 49)"/>
</g>

@if($hasText)
{{-- ── Company name text ────────────────────────────── --}}
<text x="80" y="174" text-anchor="middle"
  font-family="Tajawal,Arial,sans-serif"
  font-size="18" font-weight="900"
  fill="#1B3A70" letter-spacing="0.4">وفرة الخليجية</text>
<text x="80" y="193" text-anchor="middle"
  font-family="Tajawal,Arial,sans-serif"
  font-size="11" fill="#4A6880">للخدمات المالية</text>
@endif

<style>
/* Globe spin: translateX 142→0  =  one full rotation appearing from the right */
@keyframes wfs{{$gid}}{
  from { transform: translateX(142px); }
  to   { transform: translateX(0px);   }
}
</style>
</svg>

{{-- ── JS: generate dot positions ───────────────────── --}}
<script>
(function(){
  var g = document.getElementById('dl{{$gid}}');
  if (!g) return;

  /* [latitude_deg, dots_per_revolution, dot_radius]
     Higher latitudes (near poles) → fewer, smaller dots */
  var rows = [
    [-65, 4, 1.1],
    [-50, 7, 1.8],
    [-35, 9, 2.3],
    [-20, 11, 2.6],
    [-5,  12, 2.9],
    [ 10, 12, 2.9],
    [ 25, 10, 2.5],
    [ 40, 8,  2.1],
    [ 55, 6,  1.6],
    [ 70, 4,  1.0]
  ];

  var ns = 'http://www.w3.org/2000/svg';
  /* Sphere: R=71, center=(80,79). One full rotation = 2*71 = 142px.
     We animate translateX(142→0), so we need dots from x=-142 to x=+155
     to fill the visible circle (x 9..151) at every animation phase. */

  rows.forEach(function(row){
    var phi = row[0] * Math.PI / 180;
    var nd  = row[1];
    var dr  = row[2];
    var y   = (79 + 71 * Math.sin(phi)).toFixed(2);
    var sp  = 142 / nd;  /* x-spacing between dots */

    for (var x = -142; x <= 155; x += sp) {
      var c = document.createElementNS(ns, 'circle');
      c.setAttribute('cx', x.toFixed(1));
      c.setAttribute('cy', y);
      c.setAttribute('r',  dr);
      c.setAttribute('fill', '#B4EAF8');         /* light teal dots */
      c.setAttribute('fill-opacity', '0.78');
      g.appendChild(c);
    }
  });
})();
</script>
