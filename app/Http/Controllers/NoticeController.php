<?php
/* One more thing I have forgotten to mention. Any table used in the application should use backend datatable and form. dropdown, or select items should have bachkend ajax auto complete suggestion. */

namespace App\Http\Controllers;

use App\Models\Lease;
use App\Models\Notice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use niklasravnsborg\LaravelPdf\Facades\Pdf;

class NoticeController extends Controller
{
    // প্রিভিউ পেজ: প্রসেস নং আর ইনপুট লাগবে না (প্রিন্টের পর হাতে লেখা হবে)
    public function preview(Request $req)
    {
        $data = $req->validate([
            'lease_ids'   => 'required|array',
            'lease_ids.*' => 'exists:leases,id',
        ]);

        $by = app('calendar')->currentBanglaYear();

        // 🔴 গুরুত্বপূর্ণ: lessee.persons eager-load করলাম
        $leases = Lease::with([
                'property.plots',
                'lessee.persons',
                'notices' => fn($q) => $q->latest('issue_date')->latest('generated_at'),
            ])
            ->whereIn('id', $data['lease_ids'])
            ->get();

        // কেবল তারিখ নিয়েই কাস্টমাইজ হবে
        $defaultDateBn = 'আশ্বিন ' . $by;
        $defaultDateEn = now('Asia/Dhaka')->isoFormat('MMMM YYYY');

        $office = config('office');

        return view('notices.preview', compact('leases','by','office','defaultDateBn','defaultDateEn'));
    }

    // Temporary inline preview (no save, no DB write)
    public function previewPdf(Request $req)
    {
        $validated = $req->validate([
            'date_bn'     => 'required|string|max:255',
            'date_en'     => 'required|string|max:255',
            'lease_ids'   => 'required|array',
            'lease_ids.*' => 'exists:leases,id',
            'process_no'  => 'nullable|string|max:255',
            'issue_date'  => 'nullable|date',
            'due_year'    => 'nullable|string|max:255',
            'due_amount'  => 'nullable|string|max:255',
        ]);

        $by     = app('calendar')->currentBanglaYear();
        $office = config('office');

        // 🔴 গুরুত্বপূর্ণ: lessee.persons eager-load করলাম
        $leases = Lease::with(['property.plots','lessee.persons'])
            ->whereIn('id', $validated['lease_ids'])
            ->get();

        if ($leases->isEmpty()) {
            return back()->with('err', 'No selected leases.');
        }

        $pages = [];
        foreach ($leases as $lease) {
            $from = ($lease->last_paid_year ?? ($lease->first_year - 1)) + 1;
            $to   = $by;
            if ($to < $from) continue;

            $pages[] = [
                'property'     => $lease->property,
                'lessee'       => $lease->lessee, // persons already loaded
                'vp_case_no'   => $lease->property->vp_case_no ?? '',
                'year_ranges'  => ($from==$to) ? (string)$from : ($from.'-'.$to),
                'total_due'    => ($to - $from + 1) * (float)$lease->annual_rate,
            ];
        }
        if (empty($pages)) {
            return back()->with('err', 'All selected leases are up-to-date.');
        }

        $pdf = Pdf::loadView('pdf.notices_official', [
            'pages'   => $pages,
            'office'  => $office,
            'date_bn' => $validated['date_bn'],
            'date_en' => $validated['date_en'],
            'by'      => $by,
            'process_no' => $validated['process_no'] ?? '',
            'due_year'   => $validated['due_year'] ?? '',
            'due_amount' => $validated['due_amount'] ?? '',
        ], [], [
            'format'            => 'A4',
            'orientation'       => 'P',
            'margin_left'       => 12,
            'margin_right'      => 12,
            'margin_top'        => 12,
            'margin_bottom'     => 14,
            'default_font'      => 'nikosh',
            'autoLangToFont'    => true,
            'autoScriptToLang'  => true,
        ]);

        // IMPORTANT: stream inline so browser displays it (no download)
        return $pdf->stream('Notices_Preview.pdf'); // Content-Disposition: inline
    }

    // জেনারেট: এক PDF-এ একাধিক পেজ, 'memo_no' নেই (saves file + DB rows, then downloads)
    public function generate(Request $req)
    {
        $validated = $req->validate([
            'date_bn'     => 'required|string|max:255',
            'date_en'     => 'required|string|max:255',
            'lease_ids'   => 'required|array',
            'lease_ids.*' => 'exists:leases,id',
            'process_no'  => 'nullable|string|max:255',
            'issue_date'  => 'nullable|date',
            'due_year'    => 'nullable|string|max:255',
            'due_amount'  => 'nullable|string|max:255',
        ]);

        $by     = app('calendar')->currentBanglaYear();
        $office = config('office');

        // 🔴 গুরুত্বপূর্ণ: lessee.persons eager-load করলাম
        $leases = Lease::with(['property.plots','lessee.persons'])
            ->whereIn('id', $validated['lease_ids'])
            ->get();

        if ($leases->isEmpty()) {
            return back()->with('err','No selected leases.');
        }

        $pages = [];
        foreach ($leases as $lease) {
            $from = ($lease->last_paid_year ?? ($lease->first_year - 1)) + 1;
            $to   = $by;
            if ($to < $from) continue;

            $pages[] = [
                'property'     => $lease->property,
                'lessee'       => $lease->lessee, // persons already loaded
                'vp_case_no'   => $lease->property->vp_case_no ?? '',
                'year_ranges'  => ($from==$to) ? (string)$from : ($from.'-'.$to),
                'total_due'    => ($to - $from + 1) * (float)$lease->annual_rate,
            ];
        }
        if (empty($pages)) return back()->with('err','All selected leases are up-to-date.');

        $folder    = 'notices';
        $fileName  = 'Notices_'.now()->format('Ymd_His').'.pdf';
        $fullPath  = $folder.'/'.$fileName;
        Storage::makeDirectory($folder);

        $pdf = Pdf::loadView('pdf.notices_official', [
            'pages'   => $pages,
            'office'  => $office,
            'date_bn' => $validated['date_bn'],
            'date_en' => $validated['date_en'],
            'by'      => $by,
            'process_no' => $validated['process_no'] ?? '',
            'due_year'   => $validated['due_year'] ?? '',
            'due_amount' => $validated['due_amount'] ?? '',
        ], [], [
            'format'            => 'A4',
            'orientation'       => 'P',
            'margin_left'       => 12,
            'margin_right'      => 12,
            'margin_top'        => 12,
            'margin_bottom'     => 14,
            'default_font'      => 'nikosh',
            'autoLangToFont'    => true,
            'autoScriptToLang'  => true,
        ]);

        Storage::put($fullPath, $pdf->output());

        $userId = auth()->id() ?: null;
        foreach ($leases as $lease) {
            Notice::create([
                'lease_id'     => $lease->id,
                'generated_by' => $userId,
                'process_no'   => $validated['process_no'] ?? null,
                'issue_date'   => $validated['issue_date'] ?? null,
                'due_year'     => $validated['due_year'] ?? null,
                'due_amount'   => $validated['due_amount'] ?? null,
                'file_path'    => $fullPath,
                'generated_at' => now(),
            ]);
        }

        return response()->download(Storage::path($fullPath));
    }

    // বকেয়া রিপোর্ট টেবিল থেকে ম্যানুয়ালি "নোটিশ জারি হয়েছে" মার্ক করা
    public function markIssued(Request $req)
    {
        $validated = $req->validate([
            'lease_id'   => 'required|exists:leases,id',
            'issue_date' => 'nullable|date',
            'process_no' => 'nullable|string|max:255',
        ]);

        $issueDate = $validated['issue_date'] ?? now('Asia/Dhaka')->toDateString();

        $notice = Notice::query()
            ->where('lease_id', $validated['lease_id'])
            ->whereDate('issue_date', $issueDate)
            ->where('file_path', 'manual_notice_issue')
            ->latest('id')
            ->first();

        if ($notice) {
            $notice->update([
                'generated_by' => auth()->id() ?: null,
                'process_no'   => $validated['process_no'] ?? null,
                'generated_at' => now(),
            ]);
        } else {
            Notice::create([
                'lease_id'     => $validated['lease_id'],
                'generated_by' => auth()->id() ?: null,
                'process_no'   => $validated['process_no'] ?? null,
                'issue_date'   => $issueDate,
                'file_path'    => 'manual_notice_issue',
                'generated_at' => now(),
            ]);
        }

        return response()->json(['ok' => true]);
    }
}
