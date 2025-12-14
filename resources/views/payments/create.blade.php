<!-- One more thing I have forgotten to mention. Any table used in the application should use backend datatable and form. dropdown, or select items should have bachkend ajax auto complete suggestion. -->
@extends('layouts.app')

@section('content')
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h2 class="h4 text-success mb-0"><i class="bi bi-cash-coin"></i> লীজ নবায়ন / পেমেন্ট</h2>
    <a href="{{ route('reports.dues') }}" class="btn btn-outline-success">
      <i class="bi bi-arrow-left"></i> বকেয়া রিপোর্টে ফিরে যান
    </a>
  </div>

  <div class="card p-3 mb-3">
    <div class="row g-3">
      <div class="col-md-6"><strong>কেস:</strong> {{ $lease->property->vp_case_no }}</div>
      <div class="col-md-6"><strong>দাগ সংখ্যা:</strong> {{ $lease->property->plots()->count() }}</div>
      <div class="col-md-6"><strong>ইউনিয়ন/মৌজা:</strong> {{ $lease->property->union }} / {{ $lease->property->mouza }}</div>
      <div class="col-md-6"><strong>বার্ষিক রেট:</strong> {{ number_format($lease->annual_rate,2) }}</div>
    </div>
  </div>

  <div class="card p-3">
    <form method="POST" action="{{ route('payments.store') }}" enctype="multipart/form-data" class="row g-3">
      @csrf
      <input type="hidden" name="lease_id" value="{{ $lease->id }}">

      <div class="col-md-3">
        <label class="form-label">From Year</label>
        <input name="from_year" value="{{ $from }}" readonly class="form-control bg-body-tertiary">
      </div>
      <div class="col-md-3">
        <label class="form-label">To Year (current {{ $by }})</label>
        <input type="number" name="to_year" value="{{ $to }}" class="form-control">
      </div>
      <div class="col-md-3">
        <label class="form-label">Amount</label>
        <input name="amount_paid" value="{{ number_format($amount,2,'.','') }}" class="form-control">
        <div class="form-text">= (to_year - from_year + 1) × annual_rate</div>
      </div>
      <div class="col-md-3">
        <label class="form-label">অনুমোদনের তারিখ (AC Land)</label>
        <input type="date" name="approved_at" class="form-control">
      </div>

      <hr class="mt-2">

      <div class="col-md-4">
        <label class="form-label">রশিদ/ডিসিয়ার নং</label>
        <input name="receipt_no" class="form-control">
      </div>
      <div class="col-md-4">
        <label class="form-label">রশিদ/ডিসিয়ার তারিখ</label>
        <input type="date" name="receipt_date" class="form-control">
      </div>
      <div class="col-md-4">
        <label class="form-label">স্ক্যান কপি (PDF/JPG/PNG, Max 4MB)</label>
        <input type="file" name="scan" accept=".pdf,.jpg,.jpeg,.png" class="form-control">
      </div>

      <div class="col-12">
        <button class="btn btn-primary"><i class="bi bi-check2-circle"></i> Save Payment</button>
        <a href="{{ route('reports.dues') }}" class="btn btn-outline-success ms-2"><i class="bi bi-x-circle"></i> Cancel</a>
      </div>
    </form>
  </div>
@endsection
