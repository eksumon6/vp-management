@extends('layouts.app')

@section('content')
@php
    // ইংরেজি অংক/মাস → বাংলা
    if (!function_exists('bn')) {
        function bn($str) {
            if ($str===null) return '';
            $en = ['0','1','2','3','4','5','6','7','8','9',
                   'January','February','March','April','May','June','July','August','September','October','November','December',
                   'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec','-'];
            $bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯',
                   'জানুয়ারি','ফেব্রুয়ারি','মার্চ','এপ্রিল','মে','জুন','জুলাই','আগস্ট','সেপ্টেম্বর','অক্টোবর','নভেম্বর','ডিসেম্বর',
                   'জানু','ফেব','মার্চ','এপ্রি','মে','জুন','জুলাই','আগ','সেপ্ট','অক্টো','নভে','ডিসে','-'];
            return str_replace($en,$bn,$str);
        }
    }
    $fyStartDisp = bn(\Carbon\Carbon::parse($fyLabelStart)->isoFormat('DD MMMM YYYY'));
    $fyEndDisp   = bn(\Carbon\Carbon::parse($fyLabelEnd)->isoFormat('DD MMMM YYYY'));
@endphp

<div class="d-flex align-items-center justify-content-between mb-3">
  <h1 class="h4 text-success mb-0"><i class="bi bi-speedometer2"></i> ড্যাশবোর্ড</h1>
</div>

<div class="row g-3">
  <div class="col-md-4">
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <div class="text-muted small">মোট ভিপি প্রোপার্টি</div>
        <div class="display-6 fw-bold">{{ bn(number_format($totalProperties)) }}</div>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <div class="text-muted small">লীজের আওতাধীন ভিপি</div>
        <div class="display-6 fw-bold">{{ bn(number_format($leasedPropertyCount)) }}</div>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <div class="text-muted small mb-1">চলতি অর্থবছরের মোট আদায়</div>
        <div class="display-6 fw-bold">{{ bn(number_format($fyCollection, 2)) }}</div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mt-3">
  <div class="col-md-4">
    <a href="{{ route('properties.index') }}" class="btn btn-outline-success w-100">
      <i class="bi bi-geo-alt"></i> সম্পত্তি তালিকা
    </a>
  </div>
  <div class="col-md-4">
    <a href="{{ route('leases.index') }}" class="btn btn-outline-primary w-100">
      <i class="bi bi-card-checklist"></i> লীজ তালিকা
    </a>
  </div>
  <div class="col-md-4">
    <a href="{{ route('reports.dues') }}" class="btn btn-outline-secondary w-100">
      <i class="bi bi-file-earmark-text"></i> বকেয়া রিপোর্ট
    </a>
  </div>
</div>
@endsection
