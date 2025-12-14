<!-- One more thing I have forgotten to mention. Any table used in the application should use backend datatable and form. dropdown, or select items should have bachkend ajax auto complete suggestion. -->
@extends('layouts.app')

@section('content')
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h4 text-success mb-0"><i class="bi bi-journal-text"></i> লীজ এসাইনমেন্ট</h1>
    <a href="{{ route('leases.create') }}" class="btn btn-primary">
      <i class="bi bi-plus-circle"></i> নতুন লীজ এসাইন
    </a>
  </div>

  <div class="card p-3">
    <table id="leases-table" class="table table-striped table-hover align-middle w-100"></table>
  </div>

  <script>
  $(function(){
    $('#leases-table').DataTable({
      processing:true, serverSide:true,
      ajax:'{{ route('datatable.leases') }}',
      columns:[
        {title:'#', data:'id', name:'leases.id', width:'60px'},

        // search/order use real DB columns via "name"
        {title:'সম্পত্তি', data:'property_ref', name:'properties.vp_case_no'},
        {title:'লিজগ্রহীতা', data:'lessee_name',  name:'lessees.name'},

        // {title:'প্রথম সন', data:'first_year', name:'leases.first_year'},
        {title:'সর্বশেষ পরিশোধ', data:'last_paid_year', name:'leases.last_paid_year',
          render:(d)=> d ? d : ''},

        // computed fields: not searchable/orderable (handled server-side as display only)
        {title:'বকেয়া (বছর)', data:'years_due',  orderable:false, searchable:false},
        {title:'বকেয়া (টাকা)', data:'amount_due', orderable:false, searchable:false},

        {title:'অ্যাকশন', data:'actions', orderable:false, searchable:false}
      ],
      order:[[0,'desc']]
    });
  });
  </script>
@endsection
