<!-- One more thing I have forgotten to mention. Any table used in the application should use backend datatable and form. dropdown, or select items should have bachkend ajax auto complete suggestion. -->
@extends('layouts.app')

@section('content')
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h4 text-success mb-0"><i class="bi bi-cash-coin"></i> বকেয়া রিপোর্ট</h1>
    <div>
      <button id="btn-preview" class="btn btn-primary">
        <i class="bi bi-filetype-pdf"></i> নির্বাচিতদের নোটিশ প্রিভিউ
      </button>
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
    <table id="dues-table" class="table table-striped table-hover align-middle w-100"></table>
  </div>

  <!-- Hidden form to post selected IDs -->
  <form id="preview-form" action="{{ route('notices.preview') }}" method="POST" class="d-none">
    @csrf
  </form>

  <script>
  $(function(){
    // ---- Persistent selection store ----
    const selectedIds = new Set();

    // ---- DataTable init ----
    const table = $('#dues-table').DataTable({
      processing:true, serverSide:true,
      ajax:{
        url:'{{ route('reports.dues.data') }}',
        data:function(d){
          d.bangla_year = $('#filter-year').val();
          d.vp_case_no  = $('#filter-case').val();
          d.union       = $('#filter-union').val();
          d.mouza       = $('#filter-mouza').val();
        }
      },
      columns:[
        {
          title: '<input type="checkbox" id="chk-all-page" class="form-check-input" title="এই পেজের সব সিলেক্ট করুন">',
          data: 'checkbox', orderable:false, searchable:false, width:'36px'
        },
        {title:'কেস/দাগ', data:'case_dag'},
        {title:'ইউনিয়ন/মৌজা', data:'union_mouza'},
        {title:'লিজগ্রহীতা', data:'lessee_name'},
        {title:'প্রথম সন', data:'first_year'},
        {title:'সর্বশেষ পরিশোধ', data:'last_paid'},
        {title:'বকেয়া (বছর)', data:'years_due'},
        {title:'বকেয়া (টাকা)', data:'amount_due'},

        // সার্ভার থেকে পাওয়া row_class এখানে আনছি (UI তে দেখানো হবে না)
        {title:'_cls', data:'row_class', visible:false, searchable:false},

        {title:'অ্যাকশন', data:'actions', orderable:false, searchable:false}
      ],
      order:[[0,'desc']],

      // প্রতিটি রোতে সার্ভার দেয়া class বসিয়ে দিচ্ছি
      rowCallback: function(row, data){
        // remove previous color classes to avoid stacking (if table redraws)
        $(row).removeClass('table-danger table-info');
        if (data.row_class) {
          $(row).addClass(data.row_class);
        }
      },

      drawCallback: function(){
        // Draw-এর পর চেকবক্স স্টেট রিস্টোর
        $('#dues-table input.row-chk').each(function(){
          const id = String($(this).val());
          $(this).prop('checked', selectedIds.has(id));
        });
        // Header select-all (এই পেজে সব চেকড কিনা)
        const all = pageCheckboxes();
        const allChecked = all.length && all.filter(':checked').length === all.length;
        $('#chk-all-page').prop('checked', allChecked);
      },

      createdRow: function(row){
        // রোতে ক্লিক করলে টগল (লিংক/বাটনে ক্লিক বাদ)
        $(row).on('click', function(e){
          if ($(e.target).is('input,button,a,span,i')) return;
          const $cb = $(this).find('input.row-chk').first();
          if ($cb.length){
            $cb.prop('checked', !$cb.prop('checked')).trigger('change');
          }
        });
      }
    });

    function pageCheckboxes(){
      // শুধুমাত্র current page-এর চেকবক্স
      return $('#dues-table').find('tbody input.row-chk');
    }

    // ---- Checkbox change: keep Set in sync ----
    $('#dues-table').on('change', 'input.row-chk', function(){
      const id = String($(this).val());
      if (this.checked) selectedIds.add(id);
      else selectedIds.delete(id);

      // header select-all update
      const all = pageCheckboxes();
      const allChecked = all.length && all.filter(':checked').length === all.length;
      $('#chk-all-page').prop('checked', allChecked);
    });

    // ---- Header select-all (this page only) ----
    $('#dues-table thead').on('change', '#chk-all-page', function(){
      const checked = this.checked;
      pageCheckboxes().each(function(){
        const id = String($(this).val());
        $(this).prop('checked', checked);
        if (checked) selectedIds.add(id);
        else selectedIds.delete(id);
      });
    });

    // ---- Filter button ----
    $('#btn-apply').on('click', ()=>{
      table.ajax.reload();
    });

    // ---- Per-row "Notice" button (immediate single preview) ----
    $('#dues-table').on('click','.btn-notice', function(e){
      e.preventDefault();
      const id = String($(this).data('id'));
      if (!id) return;
      const $form = $('#preview-form');
      // পুরনো ইনপুটগুলো (lease_ids[] ছাড়া) রেখে, শুধু lease_ids[] ক্লিয়ার
      $form.find('input[name="lease_ids[]"]').remove();
      $('<input>',{type:'hidden',name:'lease_ids[]',value:id}).appendTo($form);
      $form.trigger('submit');
    });

    // ---- Bulk preview ----
    $('#btn-preview').on('click', function(){
      const ids = Array.from(selectedIds);
      if (!ids.length){
        alert('কোনো রেকর্ড নির্বাচন করা হয়নি।');
        return;
      }
      const $form = $('#preview-form');
      $form.find('input[name="lease_ids[]"]').remove();
      ids.forEach(id => $('<input>',{type:'hidden',name:'lease_ids[]',value:id}).appendTo($form));
      $form.trigger('submit');
    });

    // DataTables default alert রাখছি (debug কাজে লাগে)
    $.fn.dataTable.ext.errMode = 'alert';
  });
  </script>
@endsection
