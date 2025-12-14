<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\LeasePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class CollectionReportController extends Controller
{
    public function index(Request $request)
    {
        $tz = 'Asia/Dhaka';
        $now = now($tz);

        $fromInput = $request->get('date_from');
        $toInput   = $request->get('date_to');

        if ($fromInput && $toInput) {
            try {
                $start = Carbon::createFromFormat('d/m/Y', $fromInput, $tz)->startOfDay();
                $end   = Carbon::createFromFormat('d/m/Y', $toInput,   $tz)->endOfDay();
            } catch (\Exception $e) {
                $start = $now->copy()->startOfMonth()->startOfDay();
                $end   = $now->copy()->endOfMonth()->endOfDay();
            }
        } else {
            $start = $now->copy()->startOfMonth()->startOfDay();
            $end   = $now->copy()->endOfMonth()->endOfDay();
        }

        // recompute total on every GET render
        $totalCollection = (float) LeasePayment::query()
            ->whereBetween(DB::raw('COALESCE(receipt_date, created_at)'), [$start, $end])
            ->sum('amount_paid');

        $dateFrom = $start->format('d/m/Y');
        $dateTo   = $end->format('d/m/Y');

        return view('reports.collections', compact('dateFrom', 'dateTo', 'totalCollection'));
    }

    public function data(Request $request)
    {
        $tz = 'Asia/Dhaka';
        $from = $request->get('date_from');
        $to   = $request->get('date_to');

        try {
            $start = $from ? Carbon::createFromFormat('d/m/Y', $from, $tz)->startOfDay() : now($tz)->startOfMonth();
            $end   = $to   ? Carbon::createFromFormat('d/m/Y', $to,   $tz)->endOfDay()   : now($tz)->endOfMonth();
        } catch (\Exception $e) {
            $start = now($tz)->startOfMonth();
            $end   = now($tz)->endOfMonth();
        }

        $q = LeasePayment::query()
            ->leftJoin('leases', 'leases.id', '=', 'lease_payments.lease_id')
            ->leftJoin('properties', 'properties.id', '=', 'leases.property_id')
            ->leftJoin('lessees', 'lessees.id', '=', 'leases.lessee_id')
            ->leftJoin('lessee_people as lp', function($j){
                $j->on('lp.lessee_id', '=', 'lessees.id')
                  ->whereNull('lp.deleted_at');
            })
            ->whereBetween(DB::raw('COALESCE(lease_payments.receipt_date, lease_payments.created_at)'), [$start, $end])
            ->groupBy('lease_payments.id')
            ->select([
                'lease_payments.*', // keep all attributes so casts apply
                DB::raw('properties.vp_case_no as vp_case_no'),
                DB::raw('properties.gazette_no as property_gazette'),
                DB::raw('COALESCE(NULLIF(GROUP_CONCAT(DISTINCT lp.name ORDER BY lp.id SEPARATOR ", "), ""), lessees.name) AS lessee_names'),
            ]);

        return DataTables::eloquent($q)
            ->addColumn('vp_case_no', fn($r) => $r->vp_case_no ?? '')
            ->addColumn('lessee_names', fn($r) => $r->lessee_names ?? '')
            ->addColumn('year_range', function($r){
                $f = (int)$r->from_year;
                $t = (int)$r->to_year;
                return $t && $t !== $f ? "{$f}–{$t}" : (string)$f;
            })
            ->addColumn('missing_gazette', fn($r) => Property::isGazetteMissing($r->property_gazette ?? null))
            ->addColumn('dcr_no', fn($r) => $r->receipt_no)
            ->addColumn('dcr_date', fn($r) => $r->receipt_date ? $r->receipt_date->format('d/m/Y') : '')
            ->addColumn('amount', fn($r) => number_format((float)$r->amount_paid, 2))
            ->filter(function($query) use ($request){
                $s = $request->input('search.value');
                if(!$s) return;
                $query->where(function($qq) use ($s){
                    $qq->where('properties.vp_case_no', 'like', "%{$s}%")
                       ->orWhere('lessees.name', 'like', "%{$s}%")
                       ->orWhere('lp.name', 'like', "%{$s}%")
                       ->orWhere('lease_payments.receipt_no', 'like', "%{$s}%")
                       ->orWhere('lease_payments.from_year', 'like', "%{$s}%")
                       ->orWhere('lease_payments.to_year', 'like', "%{$s}%");
                });
            })
            ->orderColumn('vp_case_no', 'properties.vp_case_no $1')
            ->orderColumn('lessee_names', 'lessees.name $1')
            ->toJson();
    }
}
