<!DOCTYPE html>
<html lang="bn">
<meta charset="utf-8">
<style>
  /* ==== mPDF-safe typography ==== */
  body { font-family: 'nikosh','notobn'; font-size: 14pt; line-height: 1.2; }
  .fixbn { font-family: 'nikosh','notobn'; }
  .en-text { font-size: 12pt !important; }

  /* ==== Header ==== */
  .header  { text-align: center; margin-bottom: 6px; }
  .gov     { font-size: 16pt; font-weight: bold; }
  .office  { font-size: 16pt; }
  .place, .web { font-size: 16pt; }

  /* ==== Meta (process/date) ==== */
  .meta { width:100%; border-collapse: collapse; margin: 8px 0 10px 0; }
  .meta td { vertical-align: top; font-size: 14pt; }
  .right { text-align: right; }
  .blank-line { display:inline-block; min-width: 220px; border-bottom: 1px dotted #000; height: 1.1em; vertical-align: baseline; }

  /* ----- Date block (exact as reference) ----- */
  .date-cell { padding: 0; }
  .date-grid { width:100%; border-collapse: collapse; table-layout: fixed; }
  .date-grid td { padding:0; border:0; }

  /* Left label */
  .dg-label { white-space:nowrap; text-align:right; vertical-align:middle; padding-right:10px; }

  /* Right content area */
  .dg-wrap   { width:100%; text-align:center; }
  /* inner width controls the length of the horizontal line */
  .dg-inner  { width:85%; margin:0 auto; border-collapse: collapse; table-layout: fixed; }
  .dg-inner td { padding:0; border:0; }

  .dg-row  { text-align:center; }
  .dg-bn   { padding-bottom:2px; } /* text above line */
  .dg-en   { padding-top:2px; }    /* text below line */

  /* Robust midline for mPDF */
  .dg-linecell {
    height: 0.2mm;
    background: #000;
    border-top: 0.2mm solid #000;
    padding: 0;
    line-height: 0;
  }

  /* ==== Titles & sections ==== */
  h1.title {
    text-align: center;
    font-size: 20pt;
    margin: 6px 0 10px 0;
    text-decoration: underline;
    font-weight: bold;
  }
  .subject { margin: 8px 0 10px 0; font-size: 14pt; }
  .subject b { font-weight: bold; }
  .body-text { text-align: justify; margin: 8px 0 14px 0; }

  h3.tafsil-title { text-align:center; font-size: 14pt; text-decoration: underline; margin: 8px 0 6px 0; }

  /* ==== Tafsil table ==== */
  table.tafsil { width:100%; border-collapse: collapse; font-size: 14pt; }
  table.tafsil th, table.tafsil td { border:1px solid #000; padding:6px 8px; vertical-align: middle; text-align: center; }
  table.tafsil th { background:#f5f5f5; }

  /* ==== Bottom two-column area ==== */
  .bottom-grid { width:100%; border-collapse: collapse; table-layout: fixed; }
  .bottom-grid td { vertical-align: top; }
  .left-col { width: 65%; padding-right: 8px; }
  .right-col { width: 35%; text-align: center !important; }

  /* Spacing */
  .sig-spacer { height: 150mm; }     /* প্রয়োজনে 120–170mm টিউন করুন */
  .recipient { margin-top: 15mm; }
  .cc {
    margin-top: 30px;
    padding-left: 10px;
  }

  .sign { text-align: center !important; display: inline-block; width: 100%; }
  .muted { font-size: 14pt; }

  /* Helpers */
  .pagebreak { page-break-after: always; }
</style>
<body>

@php
  if (!function_exists('bn')) {
      function bn($str) {
          if ($str === null) return '';
          $en = ['0','1','2','3','4','5','6','7','8','9',
                 'January','February','March','April','May','June','July','August','September','October','November','December',
                 'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
          $bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯',
                 'জানুয়ারি','ফেব্রুয়ারি','মার্চ','এপ্রিল','মে','জুন','জুলাই','আগস্ট','সেপ্টেম্বর','অক্টোবর','নভেম্বর','ডিসেম্বর',
                 'জানু','ফেব','মার্চ','এপ্রি','মে','জুন','জুলাই','আগ','সেপ্ট','অক্টো','নভে','ডিসে'];
          $str = str_replace($en, $bn, $str);
          return $str;
      }
  }
@endphp

@foreach($pages as $page)

  {{-- Header --}}
  <div class="header fixbn">
    <div class="gov">{{ $office['gov_name'] }}</div>
    <div class="office">{{ $office['office'] }}</div>
    <div class="place">{{ $office['upazila'] }}, {{ $office['district'] }}</div>
    <div class="web en-text" style="font-size: 14pt">{{ $office['website'] }}</div>
  </div>

  {{-- Process no. + Date --}}
  <table class="meta">
    <tr>
      <td class="fixbn" style="width: 70%">
        প্রসেস নং- <span class="blank-line">&nbsp;</span>
      </td>
      <td class="right fixbn date-cell">
        <table class="date-grid" aria-hidden="true">
          <tr>
            <td class="dg-label fixbn" style="padding-top: 1.5%">তারিখঃ</td>
            <td>
              <div class="dg-wrap">
                <table class="dg-inner">
                  <tr><td class="dg-row dg-bn fixbn">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ bn($date_bn) }}</td></tr>
                  <tr><td class="dg-linecell"></td></tr>
                  <tr><td class="dg-row dg-en fixbn">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ bn($date_en) }}</td></tr>
                </table>
              </div>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  <h1 class="title fixbn">নোটিশ</h1>

  {{-- Subject --}}
  <div class="subject fixbn">
    <b>বিষয় &nbsp;&nbsp;&nbsp;&nbsp;:</b>
    {{ bn($page['vp_case_no']) }} নং ভিপি কেসের বকেয়াসহ হাল {{ bn($by) }} বঙ্গাব্দ সাল পর্যন্ত লীজ মানি পরিশোধ প্রসঙ্গে।
  </div>

  {{-- Body --}}
  <div class="body-text fixbn">
    এতদ্বারা আপনাকে জানানো যাচ্ছে যে, {{ bn($page['vp_case_no']) }} নং ভিপি কেসের নিম্ন তফসিল বর্ণিত সম্পত্তি আপনি/আপনারা দীর্ঘদিন যাবত ভোগ দখল করছেন কিন্তু কোন লীজ মানি পরিশোধ করেন নি।
    যার ফলে সরকার রাজস্ব আদায় থেকে বঞ্চিত হচ্ছে। উক্ত সম্পত্তির হাল সন পর্যন্ত (বাংলা {{ bn($by) }} বঙ্গাব্দ) লীজ মানি আগামী ০৭ কর্মদিবসের মধ্যে পরিশোধ করতে বলা হলো।
    অন্যথায় বিধি মোতাবেক লীজ বাতিলপূর্বক বকেয়া আদায়ে আইনানুগ ব্যবস্থা গ্রহণ করা হবে।
  </div>

  <h3 class="tafsil-title fixbn">তফসিল:</h3>

  {{-- Tafsil --}}
  <table class="tafsil">
    <thead class="fixbn">
      <tr>
        <th>জেলা</th>
        <th>উপজেলা</th>
        <th>মৌজা</th>
        <th>খতিয়ান</th>
        <th>দাগ</th>
        <th>পরিমাণ</th>
      </tr>
    </thead>
    <tbody>
      @php
        $plots = $page['property']->plots ?? collect();
        $rowspan = max(1, $plots->count());
      @endphp

      @if($plots->count() > 0)
        @foreach($plots as $i => $pl)
          <tr>
            @if($i === 0)
              <td class="fixbn" rowspan="{{ $rowspan }}">{{ $office['district'] }}</td>
              <td class="fixbn" rowspan="{{ $rowspan }}">{{ $office['upazila'] }}</td>
              <td class="fixbn" rowspan="{{ $rowspan }}">{{ $page['property']->mouza }}</td>
              <td class="fixbn" rowspan="{{ $rowspan }}">{{ bn($page['property']->khatian_no) }}</td>
            @endif
            <td class="fixbn">{{ bn($pl->dag_no) }}</td>
            <td class="fixbn">
              @php
                $val = rtrim(rtrim(number_format((float)($pl->area_value ?? 0), 4, '.', ''), '0'), '.');
              @endphp
              {{ bn($val) }}
              @if(($pl->area_unit ?? '')==='shotok') শতক
              @elseif(($pl->area_unit ?? '')==='sqft') বর্গফুট
              @elseif(!empty($pl->area_unit)) {{ $pl->area_unit }}
              @endif
            </td>
          </tr>
        @endforeach
      @else
        <tr>
          <td class="fixbn">{{ $office['district'] }}</td>
          <td class="fixbn">{{ $office['upazila'] }}</td>
          <td class="fixbn">{{ $page['property']->mouza }}</td>
          <td class="fixbn">{{ bn($page['property']->khatian_no) }}</td>
          <td class="fixbn">—</td>
          <td class="fixbn">—</td>
        </tr>
      @endif
    </tbody>
  </table>

  {{-- Bottom two-column area --}}
  <table class="bottom-grid" style="margin-top: 10%;">
    <tr>
      <td class="left-col">
        {{-- Recipient --}}
        <div class="recipient">
          <b class="fixbn">প্রাপক:</b><br>

          @php
            // একাধিক লিজগ্রহীতার জন্য: Lessee -> persons()
            // Lazy-load acceptable এখানে।
            $people = $page['lessee']?->persons ?? collect();
          @endphp

          @if($people->count() > 0)
            @foreach($people as $idx => $person)
              <span class="fixbn">{{ bn($idx+1) }}. {{ $person->name }}</span>
              @if(!empty($person->father_name))
                <span class="fixbn">, পিতার নাম: {{ $person->father_name }}</span>
              @endif
              <br>
              @if(!empty($person->address))
                <span class="fixbn" style="margin-left: 1.4em;">ঠিকানা: {{ $person->address }}</span><br>
              @endif
              @if(!empty($person->mobile))
                <span class="fixbn" style="margin-left: 1.4em;">মোবাইল: {{ bn($person->mobile) }}</span><br>
              @endif
            @endforeach
          @else
            {{-- Single lessee (backward compatibility) --}}
            <span class="fixbn">{{ $page['lessee']->name }}</span><br>
            @if(!empty($page['lessee']->address))
              <span class="fixbn">{{ $page['lessee']->address }}</span><br>
            @endif
            @if(!empty($page['lessee']->mobile))
              <span class="fixbn">মোবাইল: {{ bn($page['lessee']->mobile) }}</span><br>
            @endif
          @endif
        </div>

        <br><br>

        {{-- CC list --}}
        <div class="cc">
          <b class="fixbn">অনুলিপি সদয় জ্ঞতার্থে/জ্ঞাতার্থে ও কার্যার্থে:</b><br>
          <span class="fixbn">&nbsp;&nbsp;&nbsp;১. অতিরিক্ত জেলা প্রশাসক (রাজস্ব), {{ $office['district'] }}</span><br>
          <span class="fixbn">&nbsp;&nbsp;&nbsp;২. উপজেলা নির্বাহী অফিসার, {{ $office['upazila'] }}, {{ $office['district'] }}</span><br>
          <span class="fixbn">&nbsp;&nbsp;&nbsp;৩. অফিস নথি কপি</span><br>
        </div>
      </td>

      <td class="right-col" align="center">
        <div class="sig-spacer"></div>
        <div class="sign fixbn">
          ({{ $office['officer_name'] }})<br>
          {{ $office['designation'] }}<br>
          {{ $office['upazila'] }}, {{ $office['district'] }}<br>
          ফোনঃ {{ $office['phone'] }}<br>
          ইমেইলঃ <span class="en-text">{{ $office['email'] }}</span>
        </div>
      </td>
    </tr>
  </table>

  @if(!$loop->last)
    <div class="pagebreak"></div>
  @endif

@endforeach

</body>
</html>
