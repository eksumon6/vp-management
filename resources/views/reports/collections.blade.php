@extends('layouts.app')

@section('content')
@php
  if(!function_exists('bn')){
    function bn($str){
      if($str===null) return '';
      $en=['0','1','2','3','4','৫','৬','৭','৮','৯',',']; // keep EN digits; typo fixed below
      $en=['0','1','2','3','4','5','6','7','8','9',','];
      $bn=['০','১','২','৩','৪','৫','৬','৭','৮','৯','‚'];
      return str_replace($en,$bn,(string)$str);
    }
  }
@endphp

<div class="d-flex align-items-center justify-content-between mb-3">
  <h1 class="h5 text-success mb-0"><i class="bi bi-cash-coin"></i> আদায় রিপোর্ট</h1>
  <a href="{{ route('dashboard') }}" class="btn btn-outline-success"><i class="bi bi-arrow-left"></i> ড্যাশবোর্ড</a>
</div>

<div class="card p-3 mb-3">
  <form method="GET" action="{{ route('reports.collections') }}" class="row g-3 align-items-end" id="filter-form">
    <div class="col-md-3">
      <label class="form-label">তারিখ হতে</label>
      <input type="text" class="form-control" name="date_from" id="date_from" value="{{ $dateFrom }}" placeholder="dd/mm/yyyy" autocomplete="off">
    </div>
    <div class="col-md-3">
      <label class="form-label">তারিখ পর্যন্ত</label>
      <input type="text" class="form-control" name="date_to" id="date_to" value="{{ $dateTo }}" placeholder="dd/mm/yyyy" autocomplete="off">
    </div>

    <div class="col-md-4">
      <div class="d-flex gap-2 flex-wrap">
        <button type="button" class="btn btn-outline-success btn-sm" data-range="this-month">বর্তমান মাস</button>
        <button type="button" class="btn btn-outline-success btn-sm" data-range="last-month">বিগত মাস</button>
        <button type="button" class="btn btn-outline-success btn-sm" data-range="last-3">গত ৩ মাস</button>
        <button type="button" class="btn btn-outline-success btn-sm" data-range="last-6">গত ৬ মাস</button>
        <button type="button" class="btn btn-outline-success btn-sm" data-range="fiscal-year">বর্তমান অর্থবছরে আদায়</button>
      </div>
    </div>

    <div class="col-md-2 text-end">
      <button class="btn btn-primary w-100"><i class="bi bi-funnel"></i> ফিল্টার</button>
    </div>
  </form>
</div>

<div class="row g-3">
  <div class="col-md-4">
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <div class="text-muted small">নির্বাচিত তারিখ রেঞ্জে সর্বমোট আদায়</div>
        <div class="display-6 fw-bold">{{ bn(number_format($totalCollection, 2)) }}</div>
        <div class="text-muted small mt-1">
          রেঞ্জ: {{ $dateFrom }} – {{ $dateTo }}
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card p-3 mt-3">
  <div class="d-flex align-items-center justify-content-between mb-2">
    <h2 class="h6 mb-0 text-success"><i class="bi bi-list-ul"></i> পেমেন্ট তালিকা</h2>
  </div>
  <table id="payments-table" class="table table-striped table-hover align-middle w-100"></table>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
(function(){
  const fpOpts = { dateFormat: 'd/m/Y', allowInput: true, static: true };
  const fpFrom = flatpickr('#date_from', fpOpts);
  const fpTo   = flatpickr('#date_to',   fpOpts);

  function fmt(d){
     const dd = String(d.getDate()).padStart(2,'0');
     const mm = String(d.getMonth()+1).padStart(2,'0');
     const yy = d.getFullYear();
     return `${dd}/${mm}/${yy}`;
  }

  function setRange(type){
    const now = new Date();
    const y = now.getFullYear(), m = now.getMonth();
    let start, end;

    if(type==='this-month'){ start=new Date(y,m,1); end=new Date(y,m+1,0); }
    else if(type==='last-month'){ start=new Date(y,m-1,1); end=new Date(y,m,0); }
    else if(type==='last-3'){ start=new Date(y,m-2,1); end=new Date(y,m+1,0); }
    else if(type==='last-6'){ start=new Date(y,m-5,1); end=new Date(y,m+1,0); }
    else if(type==='fiscal-year'){
      const fyStartYear = (m>=6)? y : y-1;
      start=new Date(fyStartYear,6,1);   // Jul 1
      end=new Date(fyStartYear+1,5,30);  // Jun 30
    }

    fpFrom.setDate(fmt(start), true, 'd/m/Y');
    fpTo.setDate(fmt(end), true, 'd/m/Y');

    // ✅ Submit the form so the page reloads and total updates
    document.getElementById('filter-form').submit();
  }

  document.querySelectorAll('[data-range]').forEach(btn=>{
    btn.addEventListener('click', ()=> setRange(btn.dataset.range));
  });

  // Payments DataTable (server-side)
  const table = $('#payments-table').DataTable({
    processing:true,
    serverSide:true,
    ajax:{
      url:'{{ route('reports.collections.data') }}',
      data: function(d){
        d.date_from = document.getElementById('date_from').value;
        d.date_to   = document.getElementById('date_to').value;
      }
    },
    columns: [
      { title:'কেস',               data:'vp_case_no',    name:'properties.vp_case_no' },
      { title:'লিজগ্রহীতা(রা)',     data:'lessee_names',  name:'lessees.name' },
      { title:'সন (থেকে–পর্যন্ত)',   data:'year_range',    orderable:false, searchable:false },
      { title:'DCR নং',            data:'dcr_no',        name:'lease_payments.receipt_no' },
      { title:'DCR তারিখ',          data:'dcr_date',      name:'lease_payments.receipt_date' },
      { title:'টাকা',               data:'amount',        name:'lease_payments.amount_paid' }
    ],
    order:[[4,'desc']]
  });

  // ❌ Do NOT prevent default. Let form submit so total updates.
  // If you want a "AJAX-only" mode in future, we can add a tiny endpoint for the total as well.

  $.fn.dataTable.ext.errMode = 'alert';
})();
</script>
@endsection
