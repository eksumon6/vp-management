<?php

namespace App\Http\Controllers;

use App\Models\Lease;
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
        $leaseIds = $rows->pluck('id')->all();
        $appAgg   = collect();

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
            ->rawColumns(['checkbox','actions'])
            ->toJson();
    }
}
