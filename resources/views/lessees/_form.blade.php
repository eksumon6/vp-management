@csrf
@php
  // Normalize: support both $lessee and (legacy) $lesse
  $lessee = $lessee ?? ($lesse ?? null);
@endphp

<div class="row g-3">
  <div class="col-12">
    <div class="d-flex align-items-center justify-content-between">
      <label class="form-label mb-0">লিজগ্রহীতা ব্যক্তিবর্গ</label>
      <button type="button" id="btn-add-person" class="btn btn-sm btn-outline-success">
        <i class="bi bi-person-plus"></i> ব্যক্তি যোগ করুন
      </button>
    </div>
    <small class="text-muted">একজন লিজগ্রহীতার অধীনে একাধিক ব্যক্তি যোগ/মুছতে পারবেন।</small>
  </div>

  <div class="col-12">
    <div id="persons-wrap" class="vstack gap-3">
      @php
        $persons = old('persons', ($lessee && $lessee?->persons) ? $lessee->persons->toArray() : []);
        if (empty($persons)) {
          $persons = [[ 'name'=>'', 'father_name'=>'', 'nid'=>'', 'mobile'=>'', 'address'=>'' ]];
        }
      @endphp

      @foreach($persons as $i => $p)
        <div class="card p-3 person-item">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <strong>ব্যক্তি #{{ $i+1 }}</strong>
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-person" {{ $i===0 ? 'disabled' : '' }}>
              <i class="bi bi-trash"></i> মুছুন
            </button>
          </div>

          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">নাম <span class="text-danger">*</span></label>
              <input name="persons[{{ $i }}][name]" value="{{ $p['name'] ?? '' }}" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">পিতার নাম</label>
              <input name="persons[{{ $i }}][father_name]" value="{{ $p['father_name'] ?? '' }}" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">NID</label>
              <input name="persons[{{ $i }}][nid]" value="{{ $p['nid'] ?? '' }}" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">মোবাইল</label>
              <input name="persons[{{ $i }}][mobile]" value="{{ $p['mobile'] ?? '' }}" class="form-control">
            </div>
            <div class="col-md-8">
              <label class="form-label">ঠিকানা</label>
              <input name="persons[{{ $i }}][address]" value="{{ $p['address'] ?? '' }}" class="form-control">
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>

  <div class="col-12">
    <button class="btn btn-primary"><i class="bi bi-check2-circle"></i> Save</button>
  </div>
</div>

{{-- template for new person --}}
<template id="person-template">
  <div class="card p-3 person-item">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <strong></strong>
      <button type="button" class="btn btn-sm btn-outline-danger btn-remove-person">
        <i class="bi bi-trash"></i> মুছুন
      </button>
    </div>
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">নাম <span class="text-danger">*</span></label>
        <input name="__NAME__" class="form-control" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">পিতার নাম</label>
        <input name="__FATHER__" class="form-control">
      </div>
      <div class="col-md-4">
        <label class="form-label">NID</label>
        <input name="__NID__" class="form-control">
      </div>
      <div class="col-md-4">
        <label class="form-label">মোবাইল</label>
        <input name="__MOBILE__" class="form-control">
      </div>
      <div class="col-md-8">
        <label class="form-label">ঠিকানা</label>
        <input name="__ADDRESS__" class="form-control">
      </div>
    </div>
  </div>
</template>

<script>
(function(){
  const wrap = document.getElementById('persons-wrap');
  const tmpl = document.getElementById('person-template').innerHTML;
  const btnAdd = document.getElementById('btn-add-person');

  function renumber(){
    const items = wrap.querySelectorAll('.person-item');
    items.forEach((card, idx) => {
      card.querySelector('strong').textContent = 'ব্যক্তি #' + (idx+1);
    });

    // enable remove except first
    items.forEach((card, idx) => {
      const btn = card.querySelector('.btn-remove-person');
      if (btn) btn.disabled = (idx === 0);
    });
  }

  btnAdd?.addEventListener('click', () => {
    const idx = wrap.querySelectorAll('.person-item').length;
    let html = tmpl
      .replace('__NAME__',   `persons[${idx}][name]`)
      .replace('__FATHER__', `persons[${idx}][father_name]`)
      .replace('__NID__',    `persons[${idx}][nid]`)
      .replace('__MOBILE__', `persons[${idx}][mobile]`)
      .replace('__ADDRESS__',`persons[${idx}][address]`);
    const div = document.createElement('div');
    div.innerHTML = html.trim();
    wrap.appendChild(div.firstElementChild);
    renumber();
  });

  wrap?.addEventListener('click', (e) => {
    if (e.target.closest('.btn-remove-person')) {
      const card = e.target.closest('.person-item');
      card.remove();

      // reindex names to keep sequence correct
      const items = wrap.querySelectorAll('.person-item');
      items.forEach((card, idx) => {
        card.querySelector('strong').textContent = 'ব্যক্তি #' + (idx+1);
        const inputs = card.querySelectorAll('input');
        inputs[0].setAttribute('name', `persons[${idx}][name]`);
        inputs[1].setAttribute('name', `persons[${idx}][father_name]`);
        inputs[2].setAttribute('name', `persons[${idx}][nid]`);
        inputs[3].setAttribute('name', `persons[${idx}][mobile]`);
        inputs[4].setAttribute('name', `persons[${idx}][address]`);
      });

      renumber();
    }
  });

  renumber();
})();
</script>
