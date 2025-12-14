<?php

namespace App\Http\Controllers;

use App\Models\Lease;
use App\Models\Property;
use App\Models\Lessee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class LeaseController extends Controller
{
    public function index()
    {
        return view('leases.index');
    }

    public function data(Request $request)
    {
        // Join properties & lessees so we can search/order by their columns
        $q = Lease::query()
            ->leftJoin('properties', 'properties.id', '=', 'leases.property_id')
            ->leftJoin('lessees',   'lessees.id',   '=', 'leases.lessee_id')
            ->select([
                'leases.*',
                DB::raw('properties.vp_case_no as property_vp'),
                DB::raw('properties.gazette_no as property_gazette'),
                DB::raw('lessees.name as lessee_name_col'),
            ]);

        return DataTables::eloquent($q)
            // show joined values (fallback to empty string)
            ->addColumn('property_ref', fn($l) => $l->property_vp ?? '')
            ->addColumn('lessee_name',  fn($l) => $l->lessee_name_col ?? '')
            ->addColumn('missing_gazette', fn($l) => Property::isGazetteMissing($l->property_gazette ?? null))
            ->addColumn('years_due',    fn($l) => $l->years_due)
            ->addColumn('amount_due',   fn($l) => number_format($l->total_due, 2))

            ->addColumn('actions', function($l){
                $edit  = route('leases.edit', $l->id);
                $renew = route('payments.create', $l->id);

                // ✅ Show Renew button only if years_due > 0
                $btnEdit  = '<a href="'.$edit.'" class="text-indigo-600">Edit</a>';
                $btnRenew = ($l->years_due > 0)
                    ? '<a href="'.$renew.'" class="text-emerald-600 ms-2">Renew</a>'
                    : '';

                return $btnEdit . $btnRenew;
            })
            ->rawColumns(['actions'])

            ->filterColumn('property_ref', function($query, $keyword){
                $query->where('properties.vp_case_no', 'like', "%{$keyword}%");
            })
            ->filterColumn('lessee_name', function($query, $keyword){
                $query->where('lessees.name', 'like', "%{$keyword}%");
            })
            ->orderColumn('property_ref', 'properties.vp_case_no $1')
            ->orderColumn('lessee_name',  'lessees.name $1')

            ->toJson();
    }

    public function create()
    {
        return view('leases.create', [
            'selectedProperty'=>null,
            'selectedLessee'=>null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'property_id'   =>'required|exists:properties,id',
            'lessee_id'     =>'required|exists:lessees,id',
            'first_year'    =>'required|integer|min:1300|max:1700',
            'last_paid_year'=>'nullable|integer|min:1300|max:1700',
            'annual_rate'   =>'nullable|numeric|min:0',
            'approved_at'   =>'nullable|date',
        ]);

        $propRate = Property::find($data['property_id'])->annual_rate ?? 0;
        $data['annual_rate'] = $data['annual_rate'] ?? $propRate;

        if (isset($data['last_paid_year']) && $data['last_paid_year'] < $data['first_year'] - 1) {
            return back()->withErrors(['last_paid_year'=>'last_paid_year কমপক্ষে first_year-1 হতে হবে'])->withInput();
        }

        Lease::create($data);
        return redirect()->route('leases.index')->with('ok','Lease assignment created.');
    }

    public function edit(Lease $lease)
    {
        $selectedProperty = $lease->property
            ? ['id'=>$lease->property->id,'text'=>$lease->property->vp_case_no.' / '.$lease->property->dag_no.' ('.$lease->property->union.'-'.$lease->property->mouza.') | Rate: '.$lease->property->annual_rate]
            : null;
        $selectedLessee = $lease->lessee
            ? ['id'=>$lease->lessee->id,'text'=>$lease->lessee->name.' ('.$lease->lessee->mobile.')']
            : null;

        return view('leases.edit', compact('lease','selectedProperty','selectedLessee'));
    }

    public function update(Request $request, Lease $lease)
    {
        $data = $request->validate([
            'property_id'   =>'required|exists:properties,id',
            'lessee_id'     =>'required|exists:lessees,id',
            'first_year'    =>'required|integer|min:1300|max:1700',
            'last_paid_year'=>'nullable|integer|min:1300|max:1700',
            'annual_rate'   =>'required|numeric|min:0',
            'approved_at'   =>'nullable|date',
        ]);

        if (isset($data['last_paid_year']) && $data['last_paid_year'] < $data['first_year'] - 1) {
            return back()->withErrors(['last_paid_year'=>'last_paid_year কমপক্ষে first_year-1 হতে হবে'])->withInput();
        }

        $lease->update($data);
        return redirect()->route('leases.index')->with('ok','Lease assignment updated.');
    }

    public function destroy(Lease $lease)
    {
        $lease->delete();
        return back()->with('ok','Lease removed.');
    }
}
