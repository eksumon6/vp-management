@extends('layouts.app')

@section('content')
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h4 text-success mb-0"><i class="bi bi-clipboard-data"></i> নোটিশ স্ট্যাটাস রিপোর্ট</h1>
    <a href="{{ route('reports.dues') }}" class="btn btn-outline-success">
      <i class="bi bi-arrow-left"></i> বকেয়া রিপোর্টে ফিরে যান
    </a>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-md-4">
      <div class="card p-3 h-100 border-success-subtle">
        <div class="text-muted">মোট বকেয়া লিজ</div>
        <div class="fs-4 fw-bold text-success">{{ $totalDueLeases }}</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card p-3 h-100 border-primary-subtle">
        <div class="text-muted">নোটিশ জারী হয়েছে</div>
        <div class="fs-4 fw-bold text-primary">{{ $noticedLeasesCount }}</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card p-3 h-100 border-danger-subtle">
        <div class="text-muted">বকেয়া আছে কিন্তু নোটিশ হয়নি</div>
        <div class="fs-4 fw-bold text-danger">{{ $withoutNoticeLeaseCount }}</div>
      </div>
    </div>
  </div>

  <div class="card p-3 mb-3">
    <form id="filter-form" class="row g-2 align-items-end">
      <div class="col-6 col-md-3">
        <label class="form-label">বাংলা সন</label>
        <input name="bangla_year" id="filter-year" value="{{ $by }}" class="form-control" placeholder="১৪৩২">
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label">কেস নং</label>
        <input name="vp_case_no" id="filter-case" value="{{ $case }}" class="form-control" placeholder="৫৫/৬৬">
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label">ইউনিয়ন</label>
        <input name="union" id="filter-union" value="{{ $union }}" class="form-control" placeholder="ইউনিয়ন">
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label">মৌজা</label>
        <input name="mouza" id="filter-mouza" value="{{ $mouza }}" class="form-control" placeholder="মৌজা">
      </div>
      <div class="col-12 mt-2">
        <button type="button" id="btn-apply" class="btn btn-outline-success">
          <i class="bi bi-funnel"></i> ফিল্টার
        </button>
      </div>
    </form>
  </div>

  <div class="card p-3">
    <table id="notice-status-table" class="table table-striped table-hover align-middle w-100"></table>
  </div>

  <script>
    $(function(){
      const table = $('#notice-status-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
          url: '{{ route('reports.notice-status.data') }}',
          data: function (d) {
            d.bangla_year = $('#filter-year').val();
            d.vp_case_no  = $('#filter-case').val();
            d.union       = $('#filter-union').val();
            d.mouza       = $('#filter-mouza').val();
          }
        },
        columns: [
          { title: 'কেস নং', data: 'case_no' },
          { title: 'ইউনিয়ন/মৌজা', data: 'union_mouza' },
          { title: 'লিজগ্রহীতা', data: 'lessee_name' },
          { title: 'বকেয়া (বছর)', data: 'years_due' },
          { title: 'বকেয়া (টাকা)', data: 'amount_due' },
          { title: 'নোটিশ সংখ্যা', data: 'notice_count' },
          { title: 'সর্বশেষ জারির তারিখ', data: 'last_notice_issue_date' },
          { title: 'সর্বশেষ প্রসেস নং', data: 'last_notice_process_no' },
          { title: 'স্ট্যাটাস', data: 'notice_status' },
        ],
      });

      $('#btn-apply').on('click', function(){
        table.ajax.reload();
      });

      $.fn.dataTable.ext.errMode = 'alert';
    });
  </script>
@endsection
