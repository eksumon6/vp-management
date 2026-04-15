<?php

namespace App\Http\Controllers;

use App\Models\Lease;
use App\Models\Notice;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ReportController extends Controller
{
    public function dues(Request $req)
    {
        $by    = (int)($req->get('bangla_year') ?: app('calendar')->currentBanglaYear());
        $union = $req->get('union');
        $mouza = $req->get('mouza');
        $case  = $req->get('vp_case_no');

        return view('reports.dues', compact('by','union','mouza','case'));
    }

    public function duesData(Request $req)
    {
        $by    = (int)($req->get('bangla_year') ?: app('calendar')->currentBanglaYear());
        $union = $req->get('union');
        $mouza = $req->get('mouza');
        $case  = $req->get('vp_case_no');

        $q = Lease::with(['property','lessee'])->select('leases.*');

        if ($union) {
            $q->whereHas('property', fn($qq) => $qq->where('union','like',"%$union%"));
        }
        if ($mouza) {
            $q->whereHas('property', fn($qq) => $qq->where('mouza','like',"%$mouza%"));
        }
        if ($case)  {
            $q->whereHas('property', fn($qq) => $qq->where('vp_case_no','like',"%$case%"));
        }

        // years_due accessor হওয়ায় get() করে ফিল্টার
        $all  = $q->get();
        $rows = $all->filter(fn($l) => $l->years_due > 0)->values();

        // --- অ্যাপ্লিকেশন অ্যাগ্রিগেশন: শুধুমাত্র non-deleted (deleted_at IS NULL) ---
        $leaseIds      = $rows->pluck('id')->all();
        $appAgg        = collect();
        $noticeCounts  = collect();
        $latestNotices = collect();

        if (!empty($leaseIds)) {
            $appAgg = DB::table('applications')
                ->selectRaw('lease_id,
                    MAX(CASE WHEN type = "renewal" THEN 1 ELSE 0 END) AS has_renewal,
                    MAX(CASE WHEN type = "ownership_change" THEN 1 ELSE 0 END) AS has_owner')
                ->whereIn('lease_id', $leaseIds)
                ->whereNull('deleted_at') // ✅ soft-deleted আবেদন বাদ
                ->groupBy('lease_id')
                ->get()
                ->keyBy('lease_id');

            $noticeCounts = DB::table('notices')
                ->selectRaw('lease_id, COUNT(*) AS total_notices')
                ->whereIn('lease_id', $leaseIds)
                ->whereNull('deleted_at')
                ->groupBy('lease_id')
                ->get()
                ->keyBy('lease_id');

            $latestNotices = DB::table('notices')
                ->select('lease_id', 'issue_date', 'process_no')
                ->whereIn('lease_id', $leaseIds)
                ->whereNull('deleted_at')
                ->orderByDesc('issue_date')
                ->orderByDesc('generated_at')
                ->get()
                ->unique('lease_id')
                ->keyBy('lease_id');
        }

        return DataTables::of($rows)
            ->addColumn('checkbox', fn($l)=>'<input type="checkbox" class="row-chk" value="'.$l->id.'">')
            ->addColumn('case_dag', fn($l)=>$l->property?($l->property->vp_case_no.' / '.$l->property->dag_no):'')
            ->addColumn('union_mouza', fn($l)=>$l->property?($l->property->union.' / '.$l->property->mouza):'')
            ->addColumn('lessee_name', fn($l)=>$l->lessee? $l->lessee->name : '')
            ->addColumn('first_year', fn($l)=>$l->first_year)
            ->addColumn('last_paid',  fn($l)=>$l->last_paid_year ?? ($l->first_year-1))
            ->addColumn('years_due',  fn($l)=>$l->years_due)
            ->addColumn('amount_due', fn($l)=>number_format($l->total_due,2))
            ->addColumn('missing_gazette', fn($l)=> Property::isGazetteMissing($l->property?->gazette_no ?? null))

            // সার্ভার-সাইডে রো ক্লাস নির্ধারণ
            ->addColumn('row_class', function($l) use ($appAgg) {
                $agg = $appAgg->get($l->id);
                if ($agg) {
                    if ((int)$agg->has_renewal === 1) {
                        return 'table-danger'; // Renewal => লাল
                    }
                    if ((int)$agg->has_owner === 1) {
                        return 'table-info';   // Ownership change => নীল/ইনফো
                    }
                }
                return '';
            })

            ->addColumn('actions', function($l){
                $renew = route('payments.create', $l);
                $btnRenew  = '<a class="text-emerald-600" href="'.$renew.'">Renew</a>';
                $btnNotice = '<button type="button" class="text-rose-600 btn-notice" data-id="'.$l->id.'">Notice</button>';
                return $btnRenew.' | '.$btnNotice;
            })
            ->addColumn('notice_issued', function($l) use ($noticeCounts, $latestNotices){
                $count  = (int)($noticeCounts->get($l->id)->total_notices ?? 0);
                $latest = $latestNotices->get($l->id);
                $isChecked = $count > 0 ? 'checked' : '';
                $issueDate = $latest?->issue_date ?? '';
                $processNo = e($latest?->process_no ?? '');

                return '<div class="d-flex align-items-center gap-2">'
                    .'<input type="checkbox" class="form-check-input notice-issued-chk" '
                    .'data-lease-id="'.$l->id.'" '
                    .'data-issue-date="'.$issueDate.'" '
                    .'data-process-no="'.$processNo.'" '
                    .$isChecked.'>'
                    .'<small class="text-muted">'.($count > 0 ? 'হ্যাঁ' : 'না').'</small>'
                    .'</div>';
            })
            ->rawColumns(['checkbox','actions','notice_issued'])
            ->toJson();
    }

    public function noticeStatus(Request $req)
    {
        $by    = (int)($req->get('bangla_year') ?: app('calendar')->currentBanglaYear());
        $union = $req->get('union');
        $mouza = $req->get('mouza');
        $case  = $req->get('vp_case_no');

        $q = Lease::query();
        if ($union) {
            $q->whereHas('property', fn($qq) => $qq->where('union','like',"%$union%"));
        }
        if ($mouza) {
            $q->whereHas('property', fn($qq) => $qq->where('mouza','like',"%$mouza%"));
        }
        if ($case)  {
            $q->whereHas('property', fn($qq) => $qq->where('vp_case_no','like',"%$case%"));
        }

        $rows = $q->withCount('notices')->get()->filter(fn($l) => $l->years_due > 0)->values();

        $totalDueLeases         = $rows->count();
        $noticedLeasesCount     = $rows->where('notices_count', '>', 0)->count();
        $withoutNoticeLeaseCount = $totalDueLeases - $noticedLeasesCount;

        return view('reports.notice_status', compact(
            'by',
            'union',
            'mouza',
            'case',
            'totalDueLeases',
            'noticedLeasesCount',
            'withoutNoticeLeaseCount'
        ));
    }

    public function noticeStatusData(Request $req)
    {
        $by    = (int)($req->get('bangla_year') ?: app('calendar')->currentBanglaYear());
        $union = $req->get('union');
        $mouza = $req->get('mouza');
        $case  = $req->get('vp_case_no');

        $q = Lease::with(['property','lessee'])
            ->select('leases.*')
            ->addSelect([
                'notice_count' => Notice::selectRaw('COUNT(*)')
                    ->whereColumn('notices.lease_id', 'leases.id'),
                'last_notice_issue_date' => Notice::select('issue_date')
                    ->whereColumn('notices.lease_id', 'leases.id')
                    ->orderByRaw('issue_date IS NULL')
                    ->orderByDesc('issue_date')
                    ->orderByDesc('generated_at')
                    ->limit(1),
                'last_notice_process_no' => Notice::select('process_no')
                    ->whereColumn('notices.lease_id', 'leases.id')
                    ->orderByRaw('issue_date IS NULL')
                    ->orderByDesc('issue_date')
                    ->orderByDesc('generated_at')
                    ->limit(1),
            ]);

        if ($union) {
            $q->whereHas('property', fn($qq) => $qq->where('union','like',"%$union%"));
        }
        if ($mouza) {
            $q->whereHas('property', fn($qq) => $qq->where('mouza','like',"%$mouza%"));
        }
        if ($case) {
            $q->whereHas('property', fn($qq) => $qq->where('vp_case_no','like',"%$case%"));
        }

        $rows = $q->get()->filter(fn($l) => $l->years_due > 0)->values();

        return DataTables::of($rows)
            ->addColumn('case_no', fn($l) => $l->property->vp_case_no ?? '')
            ->addColumn('union_mouza', fn($l) => $l->property ? ($l->property->union.' / '.$l->property->mouza) : '')
            ->addColumn('lessee_name', fn($l) => $l->lessee->name ?? '')
            ->addColumn('years_due', fn($l) => $l->years_due)
            ->addColumn('amount_due', fn($l) => number_format($l->total_due, 2))
            ->addColumn('notice_count', fn($l) => (int)($l->notice_count ?? 0))
            ->addColumn('last_notice_issue_date', function ($l) {
                if (empty($l->last_notice_issue_date)) return '—';
                return \Carbon\Carbon::parse($l->last_notice_issue_date)->format('Y-m-d');
            })
            ->addColumn('last_notice_process_no', fn($l) => $l->last_notice_process_no ?: '—')
            ->addColumn('notice_status', fn($l) => ((int)($l->notice_count ?? 0) > 0) ? 'নোটিশ হয়েছে' : 'নোটিশ হয়নি')
            ->toJson();
    }
}
