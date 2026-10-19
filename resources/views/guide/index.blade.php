@extends('layouts.app')
@section('title','User Guide')
@section('page-title','User Guide')

@section('content')
<div style="max-width:800px;margin:0 auto;padding:24px">
  <h1 style="font-size:24px;font-weight:900;color:var(--pri2);margin-bottom:10px">📖 دليل تشغيل النظام</h1>
  <div style="background:var(--bg2);border:1px solid var(--brd1);border-radius:12px;padding:20px">
    <p style="color:var(--tx);line-height:1.9">اختبار: إذا ظهرت هذه الصفحة فالخادم يعمل والمشكلة كانت في المحتوى.</p>
    <p style="color:var(--mu);line-height:1.9;font-size:13px">Test page — diagnostic only.</p>
  </div>
</div>
@endsection
