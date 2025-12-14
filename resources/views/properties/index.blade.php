@extends('layouts.app')

@section('content')
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h4 text-success mb-0"><i class="bi bi-geo-alt"></i> সম্পত্তি তালিকা</h1>
    <a href="{{ route('properties.create') }}" class="btn btn-primary">
      <i class="bi bi-plus-circle"></i> নতুন সম্পত্তি
    </a>
  </div>

  <div class="card p-3">
    <table id="properties-table" class="table table-striped table-hover align-middle w-100"></table>
  </div>

  <script>
  $(function(){
    $('#properties-table').DataTable({
      processing: true,
      serverSide: true,
      ajax: '{{ route('datatable.properties') }}',
      columns: [
        { title:'#',               data:'id',                 name:'properties.id',          width:'60px' },
        { title:'কেস',            data:'vp_case_no',         name:'properties.vp_case_no' },
        { title:'ইউনিয়ন',         data:'union',              name:'properties.union' },
        { title:'মৌজা',           data:'mouza',              name:'properties.mouza' },
        { title:'খতিয়ান',         data:'khatian_no',         name:'properties.khatian_no' },

        // লিজগ্রহীতা (যদি থাকে) — সার্ভার থেকে 'lessee_names' কী আসে
        { title:'লিজগ্রহীতা',     data:'lessee_names',       name:'lessee_names', defaultContent:'' },

        { title:'দাগ সংখ্যা',      data:'plot_count',         name:'plot_count',             width:'90px' },
        { title:'মোট বার্ষিক রেট', data:'total_annual_rate',  name:'total_annual_rate' },
        { title:'অ্যাকশন',         data:'actions',            orderable:false, searchable:false }
      ],
      order: [[0, 'desc']],
      rowCallback: function(row, data){
        $(row).toggleClass('table-warning', !!data.missing_gazette);
      }
    });
  });
  </script>
@endsection
