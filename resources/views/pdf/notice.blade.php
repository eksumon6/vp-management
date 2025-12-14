<!-- One more thing I have forgotten to mention. Any table used in the application should use backend datatable and form. dropdown, or select items should have bachkend ajax auto complete suggestion. -->
<!DOCTYPE html>
<html lang="bn">
<meta charset="utf-8">
<style>
  body { font-family: 'solaimanlipi'; font-size: 12pt; }
  .center { text-align: center; }
  .right { text-align: right; }
  .mb-1 { margin-bottom: 6px; }
  .mb-2 { margin-bottom: 10px; }
  table { width:100%; border-collapse: collapse; }
  th, td { border:1px solid #000; padding:6px; }
  th { background:#f3f3f3; }
</style>
<body>
  <h2 class="center">উপজেলা ভূমি অফিস — নোটিশ</h2>

  <p class="mb-1"><strong>লিজগ্রহীতার নাম:</strong> {{ $lessee->name }}</p>
  @if($lessee->father_name)<p class="mb-1"><strong>পিতার নাম:</strong> {{ $lessee->father_name }}</p>@endif
  @if($lessee->nid)<p class="mb-1"><strong>NID:</strong> {{ $lessee->nid }}</p>@endif
  @if($lessee->mobile)<p class="mb-1"><strong>মোবাইল:</strong> {{ $lessee->mobile }}</p>@endif
  @if($lessee->address)<p class="mb-2"><strong>ঠিকানা:</strong> {{ $lessee->address }}</p>@endif

  <div class="mb-2">
    <strong>ভিপি কেস নং:</strong> {{ $property->vp_case_no }} |
    <strong>ইউনিয়ন:</strong> {{ $property->union }} |
    <strong>মৌজা:</strong> {{ $property->mouza }} |
    <strong>খতিয়ান নং:</strong> {{ $property->khatian_no }} |
    <strong>জে.এল. নং:</strong> {{ $property->jl_no }} |
    <strong>দাগ নং:</strong> {{ $property->dag_no }}
  </div>

  <h3 class="mb-1">তফসিল</h3>
  <table class="mb-2">
    <thead>
      <tr>
        <th>মৌজা</th>
        <th>খতিয়ান</th>
        <th>দাগ</th>
        <th>জমির পরিমাণ (ডেসিমাল)</th>
        <th>শ্রেণি</th>
        <th>সন</th>
        <th class="right">টাকার পরিমাণ</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>{{ $property->mouza }}</td>
        <td>{{ $property->khatian_no }}</td>
        <td>{{ $property->dag_no }}</td>
        <td>{{ $property->area_decimal }}</td>
        <td>{{ $property->land_class }}</td>
        <td>{{ $year_ranges }}</td>
        <td class="right">{{ number_format($total, 2) }}</td>
      </tr>
    </tbody>
    <tfoot>
      <tr>
        <th colspan="6" class="right">মোট</th>
        <th class="right">{{ number_format($total, 2) }}</th>
      </tr>
    </tfoot>
  </table>

  <p class="mb-2">
    উপরোক্ত তফসিলভুক্ত ভিপি সম্পত্তির {{ $year_ranges }} বাংলা সনের লীজ মানি এখনও পরিশোধিত হয়নি।
    অনুগ্রহপূর্বক নির্ধারিত সময়ের মধ্যে পরিশোধ/নবায়ন করুন, অন্যথায় প্রযোজ্য আইন অনুযায়ী ব্যবস্থা গ্রহণ করা হবে।
  </p>

  <p class="mb-2">তারিখ: {{ now('Asia/Dhaka')->format('d-m-Y') }}</p>
  <p class="center">— উপজেলা ভূমি অফিস</p>
</body>
</html>
