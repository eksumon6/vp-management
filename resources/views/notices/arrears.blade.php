<!-- One more thing I have forgotten to mention. Any table used in the application should use backend datatable and form. dropdown, or select items should have bachkend ajax auto complete suggestion. -->
@extends('layouts.app')

@section('content')
<div class="card p-5">
  <h1 class="text-2xl font-semibold mb-3">অবশিষ্ট (ধারাবাহিক) বকেয়া বছরের সারসংক্ষেপ</h1>

  <form id="filter-form" class="flex flex-wrap gap-2 mb-3">
    <input name="bangla_year" id="filter-year" value="{{ $by }}" class="border p-2 rounded" placeholder="১৪৩১">
    <input name="lookback" id="filter-lookback" value="{{ $lookback }}" class="border p-2 rounded" placeholder="পিছনের বছর (ডিফল্ট ৫)">
    <button type="button" id="btn-apply" class="btn btn-outline">ফিল্টার</button>
  </form>

  <table id="arrears-table" class="display w-full"></table>
</div>

<script>
$(function(){
  const table = $('#arrears-table').DataTable({
    processing: true, serverSide: true, searching: true,
    ajax: {
      url: '{{ route('reports.arrears.data') }}',
      data: function(d){
        d.bangla_year = $('#filter-year').val();
        d.lookback    = $('#filter-lookback').val();
      }
    },
    columns: [
      {title:'কেস/দাগ', data:'case_dag'},
      {title:'ইউনিয়ন/মৌজা', data:'union_mouza'},
      {title:'লিজগ্রহীতা', data:'lessee_name'},
      {title:'কত বছর বকেয়া', data:'years_due'}
    ]
  });

  $('#btn-apply').on('click', function(){ table.ajax.reload(); });
});
</script>
@endsection
