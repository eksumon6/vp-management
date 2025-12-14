<?php
/* One more thing I have forgotten to mention. Any table used in the application should use backend datatable and form. dropdown, or select items should have bachkend ajax auto complete suggestion. */

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyPlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PropertyController extends Controller
{
    public function index()
    {
        return view('properties.index');
    }

    public function data(Request $req)
    {
        // plots count + sum, and load leases->lessee (for showing lessee names)
        $q = Property::query()
            ->withCount('plots')
            ->withSum('plots', 'annual_rate')
            ->with([
                'leases' => function ($q) {
                    $q->select('id','property_id','lessee_id');
                },
                'leases.lessee' => function ($q) {
                    $q->select('id','name');
                },
            ]);

        return DataTables::eloquent($q)
            ->addColumn('id', fn($p) => $p->id)
            ->addColumn('vp_case_no', fn($p)=> $p->vp_case_no)
            ->addColumn('union', fn($p)=> $p->union)               // <-- JSON key "union"
            ->addColumn('mouza', fn($p)=> $p->mouza)
            ->addColumn('khatian_no', fn($p)=> $p->khatian_no)

            // lessee names (unique, comma-separated) if any
            ->addColumn('lessee_names', function ($p) {
                if (!$p->relationLoaded('leases')) return '';
                $names = collect($p->leases)
                    ->map(fn($l) => optional($l->lessee)->name)
                    ->filter()
                    ->unique()
                    ->values();
                return $names->isNotEmpty() ? e($names->implode(', ')) : '';
            })

            ->addColumn('plot_count', fn($p)=> $p->plots_count)
            ->addColumn('total_annual_rate', fn($p)=> number_format((float)($p->plots_sum_annual_rate ?? 0), 2))
            ->addColumn('actions', function($p){
                $edit = route('properties.edit',$p);
                $del  = route('properties.destroy',$p);
                return '<a class="btn btn-sm btn-outline-success" href="'.$edit.'">Edit</a>
                        <form method="POST" action="'.$del.'" style="display:inline-block" onsubmit="return confirm(\'Delete?\')">
                          '.csrf_field().method_field('DELETE').'
                          <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>';
            })
            // Optional: global search mapping to real columns to keep server-side filter correct
            ->filter(function($query) use ($req){
                $s = $req->input('search.value');
                if(!$s) return;
                $query->where(function($qq) use ($s){
                    $qq->where('properties.vp_case_no','like',"%{$s}%")
                       ->orWhere('properties.mouza','like',"%{$s}%")
                       ->orWhere('properties.union','like',"%{$s}%")
                       ->orWhere('properties.khatian_no','like',"%{$s}%");
                });
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function create()
    {
        return view('properties.create', ['property'=>new Property()]);
    }

    public function store(Request $req)
    {
        $validated = $req->validate([
            'vp_case_no'   => 'required|string|max:100',
            'union'        => 'nullable|string|max:100',
            'mouza'        => 'nullable|string|max:100',
            'khatian_no'   => 'nullable|string|max:100',
            'jl_no'        => 'nullable|string|max:100',
            'gazette_no'   => 'nullable|string|max:100',
            'remarks'      => 'nullable|string',
            'plots'        => 'required|array|min:1',
            'plots.*.dag_no'     => 'required|string|max:100',
            'plots.*.land_class' => 'nullable|string|max:100',
            'plots.*.area_value' => 'required|numeric|min:0',
            'plots.*.area_unit'  => 'required|in:shotok,sqft',
            'plots.*.annual_rate'=> 'required|numeric|min:0',
        ]);

        DB::transaction(function() use ($validated) {
            $property = Property::create(collect($validated)->except('plots')->toArray());
            foreach ($validated['plots'] as $pl) {
                $pl['property_id'] = $property->id;
                PropertyPlot::create($pl);
            }
        });

        return redirect()->route('properties.index')->with('ok','Property created.');
    }

    public function edit(Property $property)
    {
        $property->load('plots');
        return view('properties.edit', compact('property'));
    }

    public function update(Request $req, Property $property)
    {
        $validated = $req->validate([
            'vp_case_no'   => 'required|string|max:100',
            'union'        => 'nullable|string|max:100',
            'mouza'        => 'nullable|string|max:100',
            'khatian_no'   => 'nullable|string|max:100',
            'jl_no'        => 'nullable|string|max:100',
            'gazette_no'   => 'nullable|string|max:100',
            'remarks'      => 'nullable|string',
            'plots'        => 'required|array|min:1',
            'plots.*.id'         => 'nullable|exists:property_plots,id',
            'plots.*.dag_no'     => 'required|string|max:100',
            'plots.*.land_class' => 'nullable|string|max:100',
            'plots.*.area_value' => 'required|numeric|min:0',
            'plots.*.area_unit'  => 'required|in:shotok,sqft',
            'plots.*.annual_rate'=> 'required|numeric|min:0',
        ]);

        DB::transaction(function() use ($validated, $property) {
            $property->update(collect($validated)->except('plots')->toArray());

            // replace plots (simple)
            $property->plots()->delete();
            foreach ($validated['plots'] as $pl) {
                $pl['property_id'] = $property->id;
                PropertyPlot::create($pl);
            }
        });

        return redirect()->route('properties.index')->with('ok','Property updated.');
    }

    public function destroy(Property $property)
    {
        $property->delete();
        return back()->with('ok','Property deleted.');
    }

    // ---------- Select2 AJAX for Lease form ----------
    public function select2(Request $req)
    {
        $q    = $req->get('q', $req->get('term'));
        $page = (int)($req->get('page',1));
        $per  = 20;

        $builder = Property::query()
            ->withCount('plots')
            ->withSum('plots','annual_rate')
            ->with(['plots:id,property_id,dag_no']);

        if ($q) {
            $builder->where(function($qq) use ($q){
                $qq->where('vp_case_no','like',"%{$q}%")
                   ->orWhere('mouza','like',"%{$q}%")
                   ->orWhere('union','like',"%{$q}%")
                   ->orWhere('khatian_no','like',"%{$q}%");
            });
        }

        $builder->orderByDesc('properties.created_at')
                ->orderByDesc('properties.id');

        $total = (clone $builder)->count();

        $items = $builder->forPage($page,$per)->get()
            ->map(function($p){
                $sum = number_format((float)($p->plots_sum_annual_rate ?? 0),2);
                $dags = collect($p->plots)->pluck('dag_no')->filter()->values();
                $first = $dags->take(5)->implode(', ');
                $more  = max(0, $dags->count() - 5);
                $dagText = $first . ($more ? " +{$more}" : '');
                $dagPart = $dagText ? " — দাগ: {$dagText}" : '';
                $text = "{$p->vp_case_no} — {$p->union}/{$p->mouza}{$dagPart} — {$p->plots_count} দাগ — মোট রেট: {$sum}";
                return ['id'=>$p->id,'text'=>$text];
            });

        return response()->json([
            'results' => $items,
            'pagination' => ['more' => ($page*$per) < $total]
        ]);
    }

    // Quick fetch for lease form (total rate etc.)
    public function showJson(Property $property)
    {
        $total = (float)$property->plots()->sum('annual_rate');
        return response()->json([
            'id' => $property->id,
            'total_annual_rate' => $total,
            'vp_case_no'  => $property->vp_case_no,
            'plot_count'  => $property->plots()->count(),
        ]);
    }
}
