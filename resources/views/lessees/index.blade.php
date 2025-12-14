<!-- One more thing I have forgotten to mention. Any table used in the application should use backend datatable and form. dropdown, or select items should have bachkend ajax auto complete suggestion. -->
@extends('layouts.app')

@section('content')
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h4 text-success mb-0"><i class="bi bi-person-badge"></i> লিজগ্রহীতা</h1>
    <a href="{{ route('lessees.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> নতুন লিজগ্রহীতা</a>
  </div>

  <div class="card p-3">
    <table id="lessees-table" class="table table-striped table-hover align-middle w-100"></table>
  </div>

  <script>
  $(function(){
    $('#lessees-table').DataTable({
      processing:true, serverSide:true,
      ajax:'{{ route('datatable.lessees') }}',
      columns:[
        {title:'#', data:'id', name:'lessees.id', width:'60px'},
        {title:'নাম(সমূহ)', data:'name', name:'name'},        // aggregated persons' names
        {title:'মোবাইল(সমূহ)', data:'mobile', name:'mobile'}, // aggregated persons' mobile
        {title:'ঠিকানা', data:'address', name:'address'},     // aggregated persons' address
        {title:'অ্যাকশন', data:'actions', orderable:false, searchable:false}
      ],
      order:[[0,'desc']],
      // সার্চ persons টেবিলেও কাজ করার জন্য controller-এ কাস্টম filter যোগ করা আছে
    });
  });
  </script>
@endsection
