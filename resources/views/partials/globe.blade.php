{{--
  ╔══════════════════════════════════════════════════════════╗
  ║  Wafra Gulf — Animated Logo Component  v8.0              ║
  ║  Exact logo: white bg · teal dots · dark charcoal text   ║
  ║  Ring animates back→front · name animates in perspective ║
  ║  Usage:                                                  ║
  ║    @include('partials.globe', [                          ║
  ║      'size'      => 'xs|sm|md|lg|xl', (default: 'md')   ║
  ║      'showText'  => true|false,        (default: true)   ║
  ║      'gid'       => 'myUniqueId',      (optional)        ║
  ║      'whiteBg'   => true|false,        (default: true)   ║
  ║      'darkCtx'   => true|false,        (default: false)  ║
  ║    ])                                                     ║
  ╚══════════════════════════════════════════════════════════╝
--}}
@php
/* ── Params ───────────────────────────────────────────── */
$gid     = isset($gid)     ? preg_replace('/[^a-z0-9]/i','_',$gid) : ('wfg_'.rand(1000,9999));
$sz      = $size     ?? 'md';
$gw      = match($sz){ 'xs'=>44,'sm'=>62,'lg'=>132,'xl'=>176, default=>100 };
$hasText = $showText ?? true;
$whiteBg = $whiteBg  ?? true;   // white card wrapper (matches official logo)
$darkCtx = $darkCtx  ?? false;  // true = dark background, invert text to white
/* Also accept legacy $darkText=false as dark context */
if (isset($darkText) && $darkText === false) $darkCtx = true;
$nm_fs   = max(11, (int)round($gw * 0.20));
$tg_fs   = max(8,  (int)round($gw * 0.120));
$gap     = max(6,  (int)round($gw * 0.10));

/* ── Dot-globe generation ─────────────────────────────── */
$sR=43.0; $sCx=50.0; $sCy=48.0;
$dW=9.0;  $dH=7.4;   $dRx=1.6;
$pX=10.5; $pY=9.2;
/* Exact logo teal #1B9BA4 → near-white #E6F5F6 */
$r1=27;  $g1=155; $b1=164;
$r2=230; $g2=245; $b2=246;

$dots=[];
$rowY = $sCy - $sR + $dH/2 + 0.5;
while ($rowY <= $sCy + $sR - $dH/2 - 0.5) {
    $dy=$rowY-$sCy;
    $hw=sqrt(max(0.0,$sR*$sR-$dy*$dy));
    if ($hw > $dW/2) {
        $n = min(
            max(1,(int)floor(($hw*2-$dW*0.4)/$pX)+1),
            (int)floor($hw*2/($dW+0.8))
        );
        $sx = $sCx - ($n-1)*$pX/2;
        for ($i=0;$i<$n;$i++) {
            $cx = $sx + $i*$pX;
            $nx = ($cx-($sCx-$sR))/($sR*2);
            $ny = ($rowY-($sCy-$sR))/($sR*2);
            $lf = max(0.0,min(1.0,$nx*0.62+(1-$ny)*0.50-0.10));
            $dots[]=[
                'x'=>round($cx-$dW/2,2),
                'y'=>round($rowY-$dH/2,2),
                'c'=>sprintf('rgb(%d,%d,%d)',
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
   WFG Logo v8 — scoped to #{{$gid}}
   White background · exact logo palette · perspective anim
   ═══════════════════════════════════════════════════════ */
#{{$gid}} {
  --gw  : {{ $gw }}px;
  --teal: #1B9BA4;
  --arc : #0C7A84;
  display        : inline-flex;
  flex-direction : column;
  align-items    : center;
  gap            : {{ $gap }}px;
  @if($whiteBg)
  background     : #FFFFFF;
  border-radius  : 18px;
  padding        : calc(var(--gw)*.22) calc(var(--gw)*.28);
  box-shadow     : 0 8px 32px rgba(0,0,0,.10), 0 2px 8px rgba(0,0,0,.06);
  @endif
  animation      : wfgCardIn_{{$gid}} .8s cubic-bezier(.22,1,.36,1) both;
}
@keyframes wfgCardIn_{{$gid}} {
  from { opacity:0; transform:scale(.9) translateY(12px); }
  to   { opacity:1; transform:none; }
}

/* ── Scene ──────────────────────────────────────────── */
#{{$gid}} .wfg-scene {
  position : relative;
  width    : var(--gw);
  height   : var(--gw);
  overflow : visible;
}

/* ── SVG dot globe ──────────────────────────────────── */
#{{$gid}} .wfg-dots {
  position : absolute;
  inset    : 0;
  width    : 100%;
  height   : 100%;
  overflow : visible;
  animation: wfgGlobeIn_{{$gid}} 1s cubic-bezier(.34,1.56,.64,1) .1s both;
}
@keyframes wfgGlobeIn_{{$gid}} {
  from { opacity:0; transform:scale(.7); }
  to   { opacity:1; transform:none; }
}

/* ══════════════════════════════════════════════════════
   ORBIT ARC — dark teal, back→top→front
   Matches the diagonal swoosh band in the official logo
   ══════════════════════════════════════════════════════ */
#{{$gid}} .wfg-orbit-wrap {
  position        : absolute;
  width           : calc(var(--gw)*1.54);
  height          : calc(var(--gw)*1.54);
  top             : calc(var(--gw)*-.27);
  left            : calc(var(--gw)*-.27);
  transform-style : preserve-3d;
  perspective     : calc(var(--gw)*5);
  pointer-events  : none;
}
#{{$gid}} .wfg-orbit-ring {
  position          : absolute;
  inset             : 0;
  border-radius     : 50%;
  border            : calc(max(3px,var(--gw)*.036)) solid transparent;
  border-top-color  : var(--arc);
  border-left-color : rgba(12,122,132,.70);
  border-right-color: rgba(12,122,132,.48);
  filter            : drop-shadow(0 0 calc(var(--gw)*.04) rgba(12,122,132,.30));
  animation         : wfgOrbit_{{$gid}} 5.5s cubic-bezier(.37,0,.63,1) infinite;
}
@keyframes wfgOrbit_{{$gid}} {
  0%   { transform:rotateX(-88deg) rotateZ(6deg);  opacity:.06; }
  15%  { transform:rotateX(-55deg) rotateZ(3deg);  opacity:.55; }
  35%  { transform:rotateX(-18deg) rotateZ(1deg);  opacity:.95; }
  50%  { transform:rotateX(  8deg) rotateZ(-1deg); opacity:1.0; }
  65%  { transform:rotateX( 32deg) rotateZ(-3deg); opacity:.84; }
  80%  { transform:rotateX( 60deg) rotateZ(-5deg); opacity:.36; }
  100% { transform:rotateX( 92deg) rotateZ(-8deg); opacity:.05; }
}

/* ── Second ring (depth layer) ──────────────────────── */
#{{$gid}} .wfg-orbit-ring2 {
  position          : absolute;
  width             : calc(var(--gw)*1.30);
  height            : calc(var(--gw)*1.30);
  top               : calc(var(--gw)*-.15);
  left              : calc(var(--gw)*-.15);
  border-radius     : 50%;
  border            : calc(max(1.5px,var(--gw)*.020)) solid transparent;
  border-top-color  : rgba(12,122,132,.56);
  border-right-color: rgba(12,122,132,.32);
  animation         : wfgOrbit2_{{$gid}} 5.5s cubic-bezier(.37,0,.63,1) -.95s infinite;
  pointer-events    : none;
}
@keyframes wfgOrbit2_{{$gid}} {
  0%   { transform:rotateX(-88deg) rotateZ(-5deg); opacity:.04; }
  15%  { transform:rotateX(-52deg) rotateZ(-3deg); opacity:.42; }
  35%  { transform:rotateX(-14deg) rotateZ(-1deg); opacity:.78; }
  50%  { transform:rotateX( 10deg) rotateZ( 1deg); opacity:.88; }
  65%  { transform:rotateX( 35deg) rotateZ( 3deg); opacity:.64; }
  80%  { transform:rotateX( 65deg) rotateZ( 5deg); opacity:.26; }
  100% { transform:rotateX( 95deg) rotateZ( 7deg); opacity:.04; }
}

/* ── Company text ────────────────────────────────────── */
#{{$gid}} .wfg-text {
  text-align : center;
  direction  : rtl;
  line-height: 1.3;
  @if($hasText) display:flex; flex-direction:column; align-items:center; @else display:none; @endif
}

/* ─ Name: perspective animation (comes from back to front) */
#{{$gid}} .wfg-name {
  font-family   : 'Tajawal', 'Cairo', 'Segoe UI', Arial, sans-serif;
  font-size     : {{ $nm_fs }}px;
  font-weight   : 900;
  letter-spacing: .5px;
  white-space   : nowrap;
  @if($darkCtx)
    color : #FFFFFF;
    filter: drop-shadow(0 1px 4px rgba(27,155,164,.28));
  @else
    /* Exact logo dark charcoal */
    color : #3A3D42;
  @endif
  animation : wfgNameIn_{{$gid}} 1.1s cubic-bezier(.22,1,.36,1) .35s both;
}
@keyframes wfgNameIn_{{$gid}} {
  0%  {
    opacity  : 0;
    transform: perspective(500px) translateZ(-180px) scale(.72);
    filter   : blur(3px);
  }
  60% {
    opacity  : 1;
    filter   : blur(0);
  }
  100% {
    opacity  : 1;
    transform: perspective(500px) translateZ(0) scale(1);
    filter   : blur(0);
  }
}

/* ─ Tagline: same perspective animation, slight delay */
#{{$gid}} .wfg-tagline {
  font-family : 'Tajawal', 'Cairo', 'Segoe UI', Arial, sans-serif;
  font-size   : {{ $tg_fs }}px;
  font-weight : 500;
  letter-spacing: .4px;
  margin-top  : 2px;
  @if($darkCtx)
    color : rgba(200,228,232,.85);
  @else
    /* Exact logo medium gray */
    color : #888888;
  @endif
  animation : wfgTagIn_{{$gid}} 1.1s cubic-bezier(.22,1,.36,1) .52s both;
}
@keyframes wfgTagIn_{{$gid}} {
  0%  {
    opacity  : 0;
    transform: perspective(500px) translateZ(-120px) scale(.8);
    filter   : blur(2px);
  }
  60% { opacity:1; filter:blur(0); }
  100% {
    opacity  : 1;
    transform: perspective(500px) translateZ(0) scale(1);
    filter   : blur(0);
  }
}
</style>

{{-- ══════════════ DOM ══════════════ --}}
<div id="{{$gid}}">

  <div class="wfg-scene">

    {{-- SVG dot-globe (tiles matching official logo) --}}
    <svg class="wfg-dots" viewBox="0 0 100 100"
         xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
      <defs>
        <clipPath id="{{$gid}}_clip">
          <circle cx="{{$sCx}}" cy="{{$sCy}}" r="{{$sR + 0.5}}"/>
        </clipPath>
        <radialGradient id="{{$gid}}_glow" cx="62%" cy="38%" r="55%">
          <stop offset="0%"   stop-color="#FFFFFF" stop-opacity="0.12"/>
          <stop offset="100%" stop-color="#FFFFFF" stop-opacity="0"/>
        </radialGradient>
      </defs>
      <g clip-path="url(#{{$gid}}_clip)">
        @foreach($dots as $d)
        <rect x="{{$d['x']}}" y="{{$d['y']}}"
              width="{{$dW}}" height="{{$dH}}" rx="{{$dRx}}"
              fill="{{$d['c']}}"/>
        @endforeach
        <circle cx="{{$sCx}}" cy="{{$sCy}}" r="{{$sR}}"
                fill="url(#{{$gid}}_glow)"/>
      </g>
    </svg>

    {{-- Primary orbit arc: back → over top → front --}}
    <div class="wfg-orbit-wrap">
      <div class="wfg-orbit-ring"></div>
    </div>
    <div class="wfg-orbit-ring2"></div>

  </div>

  {{-- Company name (perspective animation: back → front) --}}
  <div class="wfg-text">
    <div class="wfg-name">وفرة الخليجية</div>
    <div class="wfg-tagline">للخدمات المالية</div>
  </div>

</div>
