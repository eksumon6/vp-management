<!-- One more thing I have forgotten to mention. Any table used in the application should use backend datatable and form. dropdown, or select items should have bachkend ajax auto complete suggestion. -->
@csrf
<div class="row g-3">
  <div class="col-md-6">
    <label class="form-label">সম্পত্তি</label>
    <select id="property_id" name="property_id" class="form-select"></select>
  </div>
  <div class="col-md-6">
    <label class="form-label">লিজগ্রহীতা</label>
    <select id="lessee_id" name="lessee_id" class="form-select"></select>
  </div>

  <div class="col-md-4">
    <label class="form-label">প্রথম সন (যেমন 1425)</label>
    <input type="number" name="first_year" value="{{ old('first_year', $lease->first_year ?? app('calendar')->currentBanglaYear()) }}" class="form-control">
  </div>
  <div class="col-md-4">
    <label class="form-label">সর্বশেষ পরিশোধিত সন (যদি থাকে)</label>
    <input type="number" name="last_paid_year" value="{{ old('last_paid_year', $lease->last_paid_year ?? '') }}" class="form-control">
  </div>
  <div class="col-md-4">
    <label class="form-label">বার্ষিক রেট (টাকা)</label>
    <input type="number" step="0.01" id="annual_rate" name="annual_rate" value="{{ old('annual_rate', $lease->annual_rate ?? 0) }}" class="form-control">
    <div class="form-text">সম্পত্তি সিলেক্ট করলে **সব দাগের রেটের যোগফল** অটো-ফিল হবে (প্রয়োজনে ম্যানুয়ালি পরিবর্তন করতে পারবেন)</div>
  </div>

  <div class="col-md-4">
    <label class="form-label">লীজ অনুমোদনের তারিখ</label>
    <input type="date" name="approved_at" value="{{ old('approved_at', $lease->approved_at ?? '') }}" class="form-control">
  </div>

  <div class="col-12">
    <button class="btn btn-primary"><i class="bi bi-check2-circle"></i> Save</button>
  </div>
</div>

<script>
function initSelect2(id, url, placeholder, initialItem){
  const $el = $(id);
  $el.select2({
    theme: 'bootstrap-5',
    placeholder, allowClear:true,
    ajax: {
      url, dataType:'json', delay:250,
      data: params => ({ q: params.term, page: params.page || 1 }),
      processResults: data => data
    }
  });
  if(initialItem){
    const opt = new Option(initialItem.text, initialItem.id, true, true);
    $el.append(opt).trigger('change');
  }
}
$(function(){
  initSelect2('#property_id','{{ route('ajax.properties') }}','সম্পত্তি খুঁজুন...', @json($selectedProperty ?? null));
  initSelect2('#lessee_id','{{ route('ajax.lessees') }}','লিজগ্রহীতা খুঁজুন...', @json($selectedLessee ?? null));

  $('#property_id').on('select2:select', function(e){
    const id = e.params.data.id;
    $.get('{{ url('/ajax/property') }}/'+id, function(p){
      if (p && p.total_annual_rate !== undefined) $('#annual_rate').val(p.total_annual_rate);
    });
  });
});
</script>
