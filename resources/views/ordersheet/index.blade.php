@extends('layouts.app')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
  <h1 class="h4 text-success mb-0">
    <i class="bi bi-journal-bookmark"></i> আদেশনামা (Order Sheet)
  </h1>
  <a href="#" class="btn btn-primary">
    <i class="bi bi-plus-circle"></i> নতুন আদেশ যোগ করুন
  </a>
</div>

<div class="card p-3">
  <table id="ordersheet-table" class="table table-bordered table-striped align-middle">
    <thead class="table-success text-center">
      <tr>
        <th style="width: 5%">আদেশ</th>
        <th style="width: 10%">তারিখ</th>
        <th style="width: 10%">আদেশের ধরণ</th>
        <th>আদেশের বিবরণ</th>
        <th style="width: 10%">প্রিন্ট</th>
      </tr>
    </thead>
    <tbody>
      {{-- Dummy Data Row 1 --}}
      <tr>
        <td class="text-center">১</td>
        <td class="text-center">১৮/০৮/২০২৫</td>
        <td>লীজ নবায়ন</td>
        <td>
            &nbsp;&nbsp;&nbsp;&nbsp;অদ্য ২/৮৮ নং ভিপি কেস নথি উপস্থাপন করা হলো। লীজ গ্রহীতা জনাব ননী গোপাল প্রাং, পিতাঃ প্রেমনাথ প্রাং, সাং- কলসা, আদমদীঘি, বগুড় এর ১৪৩১-১৪৩২ সনের লীজের অর্থ বকেয়া থাকায় গত ১৫/০৫/২০২৫ খ্রি. তারিখ ৪১৮ নং প্রসেসে নোটিশ প্রদান করা হয়। বর্তমানে তিনি তফসিল বর্ণিত কলসা মৌজার ৮ নং খতিয়ানের ৬২ নং দাগে ১৮০ বর্গফুট আধা পাকা দোকানের ১৪৩১-১৪৩২ বঙ্গাব্দ সনের লীজ অর্থ পরিশোধ করে লীজ নবায়ন করতে ইচ্ছুক।
            <br><br>
            &nbsp;&nbsp;&nbsp;&nbsp;দেখলাম, সরকারী রাজস্ব আদায়ের স্বার্থে লীজ নবায়ন করা হোক।
          <div class="mt-5">
            <div class="row text-center fw-bold small mb-5">
              <div class="col">উপস্থাপক</div>
              <div class="col">যাচাইকারী</div>
              <div class="col">অনুমোদনকারী</div>
            </div>
            <div class="row text-center text-muted small">
              <div class="col">ইব্রাহীম খলিলুল্লাহ<br>ক্রেডিট চেকিং কাম-সায়রাত সহকারী<br>উপজেলা ভূমি অফিস<br>আদমদীঘি, বগুড়া</div>
              <div class="col">মাহমুদা সুলতানা<br>সহকারী কমিশনার (ভূমি)<br>আদমদীঘি, বগুড়া</div>
              <div class="col">নিশাত আনজুম অনন্যা<br>উপজেলা নির্বাহী অফিসার<br>আদমদীঘি, বগুড়া</div>
            </div>
          </div>
        </td>
        <td class="text-center">
          <button class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-printer"></i> প্রিন্ট
          </button>
        </td>
      </tr>
      
    </tbody>
  </table>
</div>

<script>
$(function(){
  $('#ordersheet-table').DataTable({
    paging: false,
    info: false,
    searching: false,
    ordering: false
  });
});
</script>
@endsection
