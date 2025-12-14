<?php

namespace App\Http\Controllers;

use App\Models\Lessee;
use App\Models\LesseePerson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class LesseeController extends Controller
{
    public function index()
    {
        return view('lessees.index');
    }

    public function data(Request $request)
    {
        $q = Lessee::query()
            ->leftJoin('lessee_people as lp', 'lp.lessee_id', '=', 'lessees.id')
            ->select([
                'lessees.id',
                DB::raw('COALESCE(NULLIF(GROUP_CONCAT(DISTINCT lp.name   ORDER BY lp.id SEPARATOR ", "), ""), lessees.name)   AS names'),
                DB::raw('COALESCE(NULLIF(GROUP_CONCAT(DISTINCT lp.mobile ORDER BY lp.id SEPARATOR ", "), ""), lessees.mobile) AS mobiles'),
                DB::raw('COALESCE(NULLIF(GROUP_CONCAT(DISTINCT lp.address ORDER BY lp.id SEPARATOR "; "), ""), lessees.address) AS addresses'),
            ])
            ->groupBy('lessees.id');

        return DataTables::of($q)
            ->addColumn('id', fn($r) => $r->id)
            ->addColumn('name', fn($r) => e($r->names))
            ->addColumn('mobile', fn($r) => e($r->mobiles))
            ->addColumn('address', fn($r) => e($r->addresses))
            ->addColumn('actions', function ($r) {
                $edit = route('lessees.edit', $r->id);
                $del  = route('lessees.destroy', $r->id);
                $csrf = csrf_field(); $method = method_field('DELETE');
                return <<<HTML
                  <a href="{$edit}" class="text-indigo-600">Edit</a>
                  <form action="{$del}" method="POST" style="display:inline" onsubmit="return confirm('Delete?')">
                    {$csrf} {$method}
                    <button class="text-rose-600 ms-2">Delete</button>
                  </form>
                HTML;
            })
            ->filter(function ($query) use ($request) {
                $s = $request->input('search.value');
                if (!$s) return;
                $query->where(function($qq) use ($s){
                    $qq->whereRaw(
                        'EXISTS (SELECT 1 FROM lessee_people p 
                                 WHERE p.lessee_id = lessees.id 
                                   AND p.deleted_at IS NULL 
                                   AND (p.name LIKE ? OR p.mobile LIKE ? OR p.nid LIKE ? OR p.address LIKE ?))',
                        ["%{$s}%","%{$s}%","%{$s}%","%{$s}%"]
                    )
                    ->orWhere('lessees.name','like',"%{$s}%")
                    ->orWhere('lessees.mobile','like',"%{$s}%")
                    ->orWhere('lessees.nid','like',"%{$s}%")
                    ->orWhere('lessees.address','like',"%{$s}%");
                });
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function create()
    {
        return view('lessees.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'persons' => 'required|array|min:1',
            'persons.*.name'        => 'required|string|max:150',
            'persons.*.father_name' => 'nullable|string|max:150',
            'persons.*.nid'         => 'nullable|string|max:50',
            'persons.*.mobile'      => 'nullable|string|max:20',
            'persons.*.address'     => 'nullable|string',
        ]);

        DB::transaction(function () use ($data) {
            $first = $data['persons'][0] ?? [];
            $lessee = Lessee::create([
                'name'        => $first['name'] ?? null,
                'father_name' => $first['father_name'] ?? null,
                'nid'         => $first['nid'] ?? null,
                'mobile'      => $first['mobile'] ?? null,
                'address'     => $first['address'] ?? null,
            ]);

            foreach ($data['persons'] as $p) {
                $p['lessee_id'] = $lessee->id;
                LesseePerson::create($p);
            }
        });

        return redirect()->route('lessees.index')->with('ok','Lessee created.');
    }

    public function edit(Lessee $lessee)
    {
        $lessee->load('persons');
        return view('lessees.edit', compact('lessee'));
    }

    public function update(Request $request, Lessee $lessee)
    {
        $data = $request->validate([
            'persons' => 'required|array|min:1',
            'persons.*.name'        => 'required|string|max:150',
            'persons.*.father_name' => 'nullable|string|max:150',
            'persons.*.nid'         => 'nullable|string|max:50',
            'persons.*.mobile'      => 'nullable|string|max:20',
            'persons.*.address'     => 'nullable|string',
        ]);

        DB::transaction(function () use ($data, $lessee) {
            $first = $data['persons'][0] ?? [];
            $lessee->update([
                'name'        => $first['name'] ?? null,
                'father_name' => $first['father_name'] ?? null,
                'nid'         => $first['nid'] ?? null,
                'mobile'      => $first['mobile'] ?? null,
                'address'     => $first['address'] ?? null,
            ]);

            $lessee->persons()->delete();
            foreach ($data['persons'] as $p) {
                $p['lessee_id'] = $lessee->id;
                LesseePerson::create($p);
            }
        });

        return redirect()->route('lessees.index')->with('ok','Lessee updated.');
    }

    public function destroy(Lessee $lessee)
    {
        $lessee->delete();
        return back()->with('ok','Lessee deleted.');
    }

    // ---------- Select2 AJAX (Lease form-এর জন্য) ----------
    // ✅ নতুন: সর্বশেষ যোগকৃত lessee আগে দেখানো (id DESC / created_at DESC)
    public function select2(Request $request)
    {
        $term = $request->get('term', $request->get('q'));
        $page = max(1, (int)$request->get('page', 1));
        $per  = 10;

        $base = Lessee::query()
            ->leftJoin('lessee_people as lp', 'lp.lessee_id', '=', 'lessees.id')
            ->select([
                'lessees.id',
                DB::raw('COALESCE(NULLIF(GROUP_CONCAT(DISTINCT lp.name   ORDER BY lp.id SEPARATOR ", "), ""), lessees.name)   AS names'),
                DB::raw('COALESCE(NULLIF(GROUP_CONCAT(DISTINCT lp.mobile ORDER BY lp.id SEPARATOR ", "), ""), lessees.mobile) AS mobiles'),
            ])
            ->groupBy('lessees.id');

        if ($term) {
            $base->where(function($q) use ($term){
                $q->whereRaw(
                    'EXISTS (SELECT 1 FROM lessee_people p 
                             WHERE p.lessee_id = lessees.id 
                               AND p.deleted_at IS NULL 
                               AND (p.name LIKE ? OR p.mobile LIKE ? OR p.nid LIKE ?))',
                    ["%{$term}%","%{$term}%","%{$term}%"]
                )
                ->orWhere('lessees.name','like',"%{$term}%")
                ->orWhere('lessees.mobile','like',"%{$term}%")
                ->orWhere('lessees.nid','like',"%{$term}%");
            });
        }

        // 🔽 Latest first
        $base->orderByDesc('lessees.created_at')
                ->orderByDesc('lessees.id');

        $total = (clone $base)->count();
        $items = $base->skip(($page-1)*$per)->take($per)->get();

        $results = $items->map(fn($x)=>[
            'id'   => $x->id,
            'text' => trim($x->names ?: 'Unknown') . ($x->mobiles ? " ({$x->mobiles})" : '')
        ]);

        return response()->json([
            'results'=>$results,
            'pagination'=>['more'=>$page*$per<$total]
        ]);
    }
}
