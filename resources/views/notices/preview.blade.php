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
      <div class="col-md-6">
        <label class="form-label">প্রসেস নং (ঐচ্ছিক)</label>
        <input name="process_no" class="form-control" value="{{ old('process_no') }}" placeholder="উদাহরণ: ৪৫.০০.০০০০.০০১.১২.৩৪">
      </div>
      <div class="col-md-6">
        <label class="form-label">নোটিশ জারির তারিখ (ঐচ্ছিক)</label>
        <input type="date" name="issue_date" class="form-control" value="{{ old('issue_date') }}">
      </div>

      <div class="col-md-6">
        <label class="form-label">বকেয়া বছর</label>
        <input name="due_year" class="form-control" value="{{ old('due_year') }}" placeholder="উদাহরণ: ১৪২৮-১৪৩০">
      </div>
      <div class="col-md-6">
        <label class="form-label">বকেয়া টাকা</label>
        <input name="due_amount" class="form-control" value="{{ old('due_amount') }}" placeholder="উদাহরণ: ২৫,০০০">
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
                <th style="width:210px">নোটিশ জারি হয়েছে কি না</th>
              </tr>
            </thead>
            <tbody>
              @foreach($leases as $l)
                @php
                  $latestNotice = $l->notices->first();
                  $latestIssueDate = $latestNotice?->issue_date ? \Illuminate\Support\Carbon::parse($latestNotice->issue_date)->format('Y-m-d') : '';
                  $latestProcessNo = $latestNotice?->process_no ?? '';
                @endphp
                <tr>
                  <td class="text-center">
                    <input type="checkbox" class="form-check-input" name="lease_ids[]" value="{{ $l->id }}" checked>
                  </td>
                  <td>{{ $l->property->vp_case_no }}</td>
                  <td>{{ $l->lessee->name }}</td>
                  <td>{{ $l->property->union }} / {{ $l->property->mouza }}</td>
                  <td>{{ $l->property->khatian_no }}</td>
                  <td>{{ $l->property->plots->count() }}</td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <input
                        type="checkbox"
                        class="form-check-input issue-mark-chk"
                        data-lease-id="{{ $l->id }}"
                        data-issue-date="{{ $latestIssueDate }}"
                        data-process-no="{{ $latestProcessNo }}"
                        {{ $latestNotice ? 'checked' : '' }}
                      >
                      <small class="text-muted">{{ $latestNotice ? 'হ্যাঁ' : 'না' }}</small>
                    </div>
                  </td>
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

  <div class="modal fade" id="mark-issued-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">নোটিশ জারি তথ্য সংরক্ষণ</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="mark-lease-id">
          <div class="mb-3">
            <label class="form-label">নোটিশ জারির তারিখ (ঐচ্ছিক)</label>
            <input type="date" id="mark-issue-date" class="form-control">
          </div>
          <div class="mb-1">
            <label class="form-label">প্রসেস নং (ঐচ্ছিক)</label>
            <input type="text" id="mark-process-no" class="form-control" placeholder="উদাহরণ: ৪৫.০০.০০০০.০০১.১২.৩৪">
          </div>
          <small class="text-muted">তারিখ/প্রসেস নং ছাড়া সেভ করলেও নোটিশ জারি মার্ক হবে।</small>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">বন্ধ</button>
          <button type="button" class="btn btn-success" id="mark-issued-save-btn">সেভ</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    $(function(){
      const markModalEl = document.getElementById('mark-issued-modal');
      const markModal = new bootstrap.Modal(markModalEl);

      $('.issue-mark-chk').on('change', function(e){
        e.stopPropagation();
        const $cb = $(this);
        const leaseId = String($cb.data('lease-id') || '');
        if (!leaseId) return;

        $cb.prop('checked', true);
        $('#mark-lease-id').val(leaseId);
        $('#mark-issue-date').val($cb.data('issue-date') || '');
        $('#mark-process-no').val($cb.data('process-no') || '');
        markModal.show();
      });

      $('#mark-issued-save-btn').on('click', function(){
        const leaseId = $('#mark-lease-id').val();
        if (!leaseId) return;

        const payload = {
          lease_id: leaseId,
          issue_date: $('#mark-issue-date').val(),
          process_no: $('#mark-process-no').val(),
          _token: '{{ csrf_token() }}'
        };

        const $btn = $(this).prop('disabled', true).text('সেভ হচ্ছে...');
        $.post('{{ route('notices.mark-issued') }}', payload)
          .done(function(){
            markModal.hide();
            window.location.reload();
          })
          .fail(function(xhr){
            const msg = xhr.responseJSON?.message || 'সংরক্ষণ করা যায়নি। আবার চেষ্টা করুন।';
            alert(msg);
          })
          .always(function(){
            $btn.prop('disabled', false).text('সেভ');
          });
      });
    });
  </script>
@endsection
