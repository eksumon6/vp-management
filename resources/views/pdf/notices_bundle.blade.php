<!-- One more thing I have forgotten to mention. Any table used in the application should use backend datatable and form. dropdown, or select items should have bachkend ajax auto complete suggestion. -->
<!DOCTYPE html>
<html lang="bn">
<meta charset="utf-8">
<style>
  body { font-family: 'nikosh'; font-size: 12pt; }
  .center { text-align: center; }
  .right { text-align: right; }
  .mb-1 { margin-bottom: 6px; }
  .mb-2 { margin-bottom: 10px; }
  table { width:100%; border-collapse: collapse; }
  th, td { border:1px solid #000; padding:6px; vertical-align: top; }
  th { background:#f3f3f3; }
  .pagebreak { page-break-after: always; }
</style>
<body>

@foreach($pages as $page)
  <h2 class="center">উপজেলা ভূমি অফিস — নোটিশ</h2>

  <p class="mb-1"><strong>লিজগ্রহীতার নাম:</strong> {{ $page['lessee']->name }}</p>
  @if($page['lessee']->father_name)<p class="mb-1"><strong>পিতার নাম:</strong> {{ $page['lessee']->father_name }}</p>@endif
  @if($page['lessee']->nid)<p class="mb-1"><strong>NID:</strong> {{ $page['lessee']->nid }}</p>@endif
  @if($page['lessee']->mobile)<p class="mb-1"><strong>মোবাইল:</strong> {{ $page['lessee']->mobile }}</p>@endif
  @if($page['lessee']->address)<p class="mb-2"><strong>ঠিকানা:</strong> {{ $page['lessee']->address }}</p>@endif

  <div class="mb-2">
    <strong>ভিপি কেস নং:</strong> {{ $page['property']->vp_case_no }} |
    <strong>ইউনিয়ন:</strong> {{ $page['property']->union }} |
    <strong>মৌজা:</strong> {{ $page['property']->mouza }} |
    <strong>খতিয়ান নং:</strong> {{ $page['property']->khatian_no }} |
    <strong>জে.এল. নং:</strong> {{ $page['property']->jl_no }}
  </div>

  <h3 class="mb-1">তফসিল</h3>
  <table class="mb-2">
    <thead>
      <tr>
        <th>দাগ নং</th>
        <th>জমির শ্রেণি</th>
        <th class="right">জমির পরিমাণ</th>
        <th>পরিমাপক</th>
      </tr>
    </thead>
    <tbody>
      @foreach($page['property']->plots as $pl)
        <tr>
          <td>{{ $pl->dag_no }}</td>
          <td>{{ $pl->land_class }}</td>
          <td class="right">{{ rtrim(rtrim(number_format((float)$pl->area_value,4,'.',''), '0'), '.') }}</td>
          <td>
            @if($pl->area_unit==='shotok') শতক
            @elseif($pl->area_unit==='sqft') বর্গফুট
            @else {{ $pl->area_unit }}
            @endif
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <p class="mb-2">তারিখ: {{ now('Asia/Dhaka')->format('d-m-Y') }}</p>
  <p class="center">— উপজেলা ভূমি অফিস</p>

  @if(!$loop->last)
    <div class="pagebreak"></div>
  @endif
@endforeach

</body>
</html>
