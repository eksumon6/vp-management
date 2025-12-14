<!-- One more thing I have forgotten to mention. Any table used in the application should use backend datatable and form. dropdown, or select items should have bachkend ajax auto complete suggestion. -->
@csrf
<div class="row g-3">
  <div class="col-md-4">
    <label class="form-label">ভিপি কেস নং</label>
    <input name="vp_case_no" value="{{ old('vp_case_no', $property->vp_case_no ?? '') }}" class="form-control">
  </div>
  <div class="col-md-4">
    <label class="form-label">ইউনিয়ন</label>
    <input name="union" value="{{ old('union', $property->union ?? '') }}" class="form-control">
  </div>
  <div class="col-md-4">
    <label class="form-label">মৌজা</label>
    <input name="mouza" value="{{ old('mouza', $property->mouza ?? '') }}" class="form-control">
  </div>

  <div class="col-md-4">
    <label class="form-label">খতিয়ান নং</label>
    <input name="khatian_no" value="{{ old('khatian_no', $property->khatian_no ?? '') }}" class="form-control">
  </div>
  <div class="col-md-4">
    <label class="form-label">জে.এল. নং</label>
    <input name="jl_no" value="{{ old('jl_no', $property->jl_no ?? '') }}" class="form-control">
  </div>
  <div class="col-md-4">
    <label class="form-label">গেজেট নং</label>
    <input name="gazette_no" value="{{ old('gazette_no', $property->gazette_no ?? '') }}" class="form-control">
  </div>

  <div class="col-12">
    <label class="form-label">Remarks</label>
    <textarea name="remarks" class="form-control" rows="2">{{ old('remarks', $property->remarks ?? '') }}</textarea>
  </div>

  <div class="col-12">
    <hr>
    <div class="d-flex align-items-center justify-content-between">
      <h5 class="text-success mb-0"><i class="bi bi-grid"></i> দাগসমূহ</h5>
      <div><strong>মোট বার্ষিক রেট: </strong><span id="total-rate">0.00</span></div>
    </div>

    <div class="table-responsive mt-2">
      <table class="table table-bordered align-middle" id="plots-table">
        <thead class="table-success">
          <tr>
            <th style="width:16%">দাগ নং</th>
            <th style="width:22%">জমির শ্রেণি</th>
            <th style="width:16%">জমির পরিমাণ</th>
            <th style="width:16%">পরিমাপক</th>
            <th style="width:16%">বার্ষিক রেট (টাকা)</th>
            <th style="width:8%"></th>
          </tr>
        </thead>
        <tbody id="plots-body">
          @php
            $oldPlots = old('plots');
            $rows = $oldPlots ?: (isset($property) ? $property->plots->map(fn($pl)=>$pl->toArray())->all() : []);
            if(!$rows || !count($rows)) { $rows = [['dag_no'=>'','land_class'=>'','area_value'=>'','area_unit'=>'shotok','annual_rate'=>'']]; }
          @endphp
          @foreach($rows as $i => $pl)
            <tr>
              <td><input name="plots[{{ $i }}][dag_no]" class="form-control" value="{{ $pl['dag_no'] ?? '' }}"></td>
              <td><input name="plots[{{ $i }}][land_class]" class="form-control" value="{{ $pl['land_class'] ?? '' }}" placeholder="উদাহরণ: আবাসিক/বাণিজ্যিক"></td>
              <td><input name="plots[{{ $i }}][area_value]" type="number" step="0.0001" class="form-control" value="{{ $pl['area_value'] ?? '' }}"></td>
              <td>
                <select name="plots[{{ $i }}][area_unit]" class="form-select">
                  <option value="shotok" {{ ($pl['area_unit'] ?? '')=='shotok'?'selected':'' }}>শতক</option>
                  <option value="sqft"   {{ ($pl['area_unit'] ?? '')=='sqft'?'selected':'' }}>বর্গফুট</option>
                </select>
              </td>
              <td><input name="plots[{{ $i }}][annual_rate]" type="number" step="0.01" class="form-control plot-rate" value="{{ $pl['annual_rate'] ?? '' }}"></td>
              <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm btn-remove-row"><i class="bi bi-x"></i></button>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <button type="button" class="btn btn-outline-success" id="btn-add-row"><i class="bi bi-plus-circle"></i> নতুন দাগ যোগ</button>
  </div>

  <div class="col-12 mt-3">
    <button class="btn btn-primary"><i class="bi bi-check2-circle"></i> Save</button>
  </div>
</div>

<script>
(function(){
  const body = document.getElementById('plots-body');
  const addBtn = document.getElementById('btn-add-row');
  const totalSpan = document.getElementById('total-rate');

  function fmt(n){ return (parseFloat(n||0).toFixed(2)); }

  function recalc(){
    let sum = 0;
    body.querySelectorAll('.plot-rate').forEach(i => sum += parseFloat(i.value||0));
    totalSpan.textContent = fmt(sum);
  }

  function addRow(values = {dag_no:'', land_class:'', area_value:'', area_unit:'shotok', annual_rate:''}){
    const idx = body.querySelectorAll('tr').length;
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><input name="plots[${idx}][dag_no]" class="form-control" value="${values.dag_no||''}"></td>
      <td><input name="plots[${idx}][land_class]" class="form-control" value="${values.land_class||''}" placeholder="উদাহরণ: আবাসিক/বাণিজ্যিক"></td>
      <td><input name="plots[${idx}][area_value]" type="number" step="0.0001" class="form-control" value="${values.area_value||''}"></td>
      <td>
        <select name="plots[${idx}][area_unit]" class="form-select">
          <option value="shotok" ${(values.area_unit||'shotok')==='shotok'?'selected':''}>শতক</option>
          <option value="sqft" ${(values.area_unit||'shotok')==='sqft'?'selected':''}>বর্গফুট</option>
        </select>
      </td>
      <td><input name="plots[${idx}][annual_rate]" type="number" step="0.01" class="form-control plot-rate" value="${values.annual_rate||''}"></td>
      <td class="text-center">
        <button type="button" class="btn btn-outline-danger btn-sm btn-remove-row"><i class="bi bi-x"></i></button>
      </td>
    `;
    body.appendChild(tr);
    tr.querySelector('.plot-rate').addEventListener('input', recalc);
    recalc();
  }

  addBtn?.addEventListener('click', ()=> addRow());

  body?.addEventListener('click', function(e){
    if (e.target.closest('.btn-remove-row')){
      const rows = body.querySelectorAll('tr');
      if (rows.length <= 1) { alert('কমপক্ষে ১টি দাগ থাকতে হবে'); return; }
      e.target.closest('tr').remove();
      recalc();
    }
  });

  // init listeners and initial calc
  body.querySelectorAll('.plot-rate').forEach(i => i.addEventListener('input', recalc));
  recalc();
})();
</script>
