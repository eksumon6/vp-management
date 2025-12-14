@extends('layouts.app')

@section('content')
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h5 text-success mb-0"><i class="bi bi-envelope-plus"></i> নতুন আবেদন</h1>
    <a href="{{ route('applications.index') }}" class="btn btn-outline-success">
      <i class="bi bi-arrow-left"></i> ফিরে যান
    </a>
  </div>

  <div class="card p-3">
    <form method="POST" action="{{ route('applications.store') }}" enctype="multipart/form-data" id="app-form">
      @csrf

      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">আবেদনের ধরন</label>
          <div class="d-flex gap-3">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="type" id="type_renewal" value="renewal" checked>
              <label for="type_renewal" class="form-check-label">লীজ নবায়নের আবেদন</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="type" id="type_owner" value="ownership_change">
              <label for="type_owner" class="form-check-label">মালিকানা পরিবর্তনের আবেদন</label>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <label class="form-label">সম্পত্তি নির্বাচন</label>
          <select name="property_id" id="property_id" class="form-select"></select>
        </div>

        <div class="col-md-4">
          <label class="form-label">তারিখ</label>
          <input type="text" name="app_date" id="app_date" class="form-control" placeholder="dd/mm/yyyy" autocomplete="off" required>
        </div>

        <div class="col-12">
          <label class="form-label">সংক্ষিপ্ত নোট</label>
          <textarea name="note" class="form-control" rows="3" placeholder="প্রয়োজনে সংক্ষিপ্ত বর্ণনা লিখুন...">{{ old('note') }}</textarea>
        </div>

        <div class="col-md-6">
          <label class="form-label">আবেদন (PDF, সর্বোচ্চ ৩MB)</label>
          <input type="file" name="application_pdf" class="form-control" accept="application/pdf" required>
        </div>

        {{-- renewal only --}}
        <div class="col-md-6 renew-only d-none">
          <label class="form-label">সর্বশেষ DCR (PDF, ঐচ্ছিক)</label>
          <input type="file" name="dcr_pdf" class="form-control" accept="application/pdf">
        </div>

        {{-- ownership change only --}}
        <div class="col-md-6 owner-only d-none">
          <label class="form-label">প্রমাণক দলিলাদি (একাধিক PDF, প্রত্যেকটি সর্বোচ্চ ৩MB)</label>
          <input type="file" name="extra_docs[]" class="form-control" accept="application/pdf" multiple>
        </div>

        <div class="col-12">
          <div id="dues-box" class="alert alert-secondary d-none mb-0"></div>
        </div>

        <div class="col-12">
          <button class="btn btn-primary"><i class="bi bi-save"></i> সংরক্ষণ করুন</button>
        </div>
      </div>
    </form>
  </div>

  {{-- Select2 & Flatpickr --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

  <script>
  $(function(){
    // datepicker dd/mm/yyyy
    flatpickr('#app_date',{ dateFormat:'d/m/Y', allowInput:true, static:true });

    // Select2 (backend AJAX already exists in your app)
    $('#property_id').select2({
      theme:'bootstrap-5',
      placeholder:'ভিপি কেস/মৌজা/ইউনিয়ন টাইপ করুন...',
      ajax:{
        url: '{{ route('ajax.properties') }}',
        dataType:'json',
        delay:200,
        data: params => ({ q: params.term, page: params.page || 1 }),
        processResults: data => data
      }
    });

    // toggle file inputs by type
    function toggleType(){
      const t = $('input[name="type"]:checked').val();
      $('.renew-only').toggleClass('d-none', t!=='renewal');
      $('.owner-only').toggleClass('d-none', t!=='ownership_change');
    }
    $('input[name="type"]').on('change', toggleType);
    toggleType();

    // dues fetch on property change
    $('#property_id').on('select2:select', function(e){
      const pid = e.params.data.id;
      $('#dues-box').removeClass('d-none').text('লোড হচ্ছে...');
      $.get('{{ url('/ajax/property-dues') }}/'+pid, function(res){
        if(!res.has_lease){
          $('#dues-box').removeClass('alert-secondary').addClass('alert-warning')
            .text(res.message || 'কোনো লীজ পাওয়া যায়নি।');
          return;
        }
        const yrs = res.years_due ?? 0;
        const amt = res.amount_due ?? 0;
        $('#dues-box').removeClass('alert-warning').addClass('alert-secondary')
          .html(
            '<b>বকেয়া অবস্থা:</b> ' +
            'বছর: <span class="text-danger fw-bold">'+yrs+'</span>, ' +
            'টাকা: <span class="text-danger fw-bold">'+Number(amt).toLocaleString()+'</span>' +
            (res.lessee_name ? ' — লিজগ্রহীতা: '+res.lessee_name : '')
          );
      });
    });
  });
  </script>
@endsection
