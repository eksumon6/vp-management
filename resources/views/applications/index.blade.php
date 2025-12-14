@extends('layouts.app')

@section('content')
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h5 text-success mb-0"><i class="bi bi-envelope-check"></i> আবেদনসমূহ</h1>
    <a href="{{ route('applications.create') }}" class="btn btn-primary">
      <i class="bi bi-plus-circle"></i> নতুন আবেদন
    </a>
  </div>

  <div class="card p-3">
    <table id="apps-table" class="table table-striped table-hover align-middle w-100"></table>
  </div>

  <script>
  $(function(){
    $('#apps-table').DataTable({
      processing:true, serverSide:true,
      ajax:'{{ route('applications.data') }}',
      columns:[
        {title:'#', data:'id', name:'applications.id', width:'60px'},
        {title:'কেস', data:'vp_case_no', name:'properties.vp_case_no'},
        {title:'লিজগ্রহীতা', data:'lessee_name', name:'lessees.name'},
        {title:'আবেদনের ধরন', data:'type_label', name:'applications.type'},
        {title:'তারিখ', data:'app_date_fmt', name:'applications.app_date'},
        {title:'নোট', data:'note', name:'applications.note',
          render:(d)=> d ? $('<div>').text(d).html() : ''},
        {title:'ফাইলসমূহ', data:'files', orderable:false, searchable:false},
        {title:'অ্যাকশন', data:'actions', orderable:false, searchable:false}
      ],
      order:[[0,'desc']],
      rowCallback: function(row, data){
        $(row).toggleClass('table-warning', !!data.missing_gazette);
      }
    });
  });
  </script>
@endsection
