<!-- One more thing I have forgotten to mention. Any table used in the application should use backend datatable and form. dropdown, or select items should have bachkend ajax auto complete suggestion. -->
@extends('layouts.app')

@section('content')
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h5 text-success mb-0"><i class="bi bi-filetype-pdf"></i> নোটিশ প্রিভিউ / সেটিংস</h1>
    <a href="{{ route('reports.dues') }}" class="btn btn-outline-success"><i class="bi bi-arrow-left"></i> ফিরে যান</a>
  </div>

  {{-- Flash errors --}}
  @if(session('err'))
    <div class="alert alert-danger">{{ session('err') }}</div>
  @endif

  {{-- Validation errors --}}
  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="card p-3 mb-3">
    {{-- One form handles both Preview (popup) and Final Generate (download) --}}
    <form id="gen-form" method="POST" action="{{ route('notices.generate') }}" class="row g-3" target="_self">
      @csrf

      <div class="col-md-6">
        <label class="form-label">তারিখ (বাংলা)</label>
        <input name="date_bn" id="date_bn_field" class="form-control" value="{{ old('date_bn', $defaultDateBn) }}" placeholder="আশ্বিন {{ $by }}">
      </div>
      <div class="col-md-6">
        <label class="form-label">তারিখ (ইংরেজি)</label>
        <input name="date_en" id="date_en_field" class="form-control" value="{{ old('date_en', $defaultDateEn) }}" placeholder="{{ now('Asia/Dhaka')->isoFormat('MMMM YYYY') }}">
      </div>

      <div class="col-12">
        <div class="table-responsive">
          <table class="table table-bordered align-middle">
            <thead class="table-success">
              <tr>
                <th style="width:50px"></th>
                <th>ভিপি কেস</th>
                <th>লিজগ্রহীতা</th>
                <th>ইউনিয়ন/মৌজা</th>
                <th>খতিয়ান</th>
                <th>দাগ সংখ্যা</th>
              </tr>
            </thead>
            <tbody>
              @foreach($leases as $l)
                <tr>
                  <td class="text-center">
                    <input type="checkbox" class="form-check-input" name="lease_ids[]" value="{{ $l->id }}" checked>
                  </td>
                  <td>{{ $l->property->vp_case_no }}</td>
                  <td>{{ $l->lessee->name }}</td>
                  <td>{{ $l->property->union }} / {{ $l->property->mouza }}</td>
                  <td>{{ $l->property->khatian_no }}</td>
                  <td>{{ $l->property->plots->count() }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>

      <div class="col-12 d-flex gap-2">
        {{-- Preview in popup (no JS stack required) --}}
        <button
          type="submit"
          class="btn btn-outline-primary"
          formaction="{{ route('notices.preview.pdf') }}"
          formtarget="pdfPreviewWin"
          onclick="window.open('', 'pdfPreviewWin', 'width=1000,height=900,noopener,noreferrer');"
        >
          <i class="bi bi-eye"></i> Preview PDF (Popup)
        </button>

        {{-- Final generate & download --}}
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-printer"></i> PDF জেনারেট (Download)
        </button>
      </div>

      <div class="col-12">
        <small class="text-muted">
          মোট লিজ: {{ $leases->count() }} | হাল সন: {{ $by }} বঙ্গাব্দ
        </small>
      </div>
    </form>
  </div>
@endsection
