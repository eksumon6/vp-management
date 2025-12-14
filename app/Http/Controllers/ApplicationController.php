<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Lease;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ApplicationController extends Controller
{
    public function index(){
        return view('applications.index');
    }

    // DataTables JSON
    public function data(Request $req)
    {
        $q = Application::query()
            ->leftJoin('properties','properties.id','=','applications.property_id')
            ->leftJoin('leases','leases.id','=','applications.lease_id')
            ->leftJoin('lessees','lessees.id','=','leases.lessee_id')
            ->select([
                'applications.*',
                DB::raw('properties.vp_case_no as vp_case_no'),
                DB::raw('properties.gazette_no as property_gazette'),
                DB::raw('lessees.name as lessee_name'),
            ]);

        return DataTables::eloquent($q)
            ->addColumn('vp_case_no', fn($r) => $r->vp_case_no ?? '')
            ->addColumn('lessee_name', fn($r) => $r->lessee_name ?? '')
            ->addColumn('missing_gazette', fn($r) => Property::isGazetteMissing($r->property_gazette ?? null))
            ->addColumn('type_label', fn($r) => $r->type === 'renewal' ? 'লীজ নবায়নের আবেদন' : 'মালিকানা পরিবর্তনের আবেদন')
            ->addColumn('app_date_fmt', fn($r) => optional($r->app_date)->format('d/m/Y'))
            ->addColumn('files', function($r){
                $links = [];
                if($r->application_pdf){
                    $links[] = '<a href="'.Storage::url($r->application_pdf).'" target="_blank">আবেদন (PDF)</a>';
                }
                if($r->dcr_pdf){
                    $links[] = '<a href="'.Storage::url($r->dcr_pdf).'" target="_blank">DCR (PDF)</a>';
                }
                if(!empty($r->extra_docs) && is_array($r->extra_docs)){
                    foreach($r->extra_docs as $i=>$p){
                        $links[] = '<a href="'.Storage::url($p).'" target="_blank">দলিল '.$i+1 .'</a>';
                    }
                }
                return implode(' | ', $links);
            })
            ->addColumn('actions', function($r){
                $del = route('applications.destroy', $r->id);
                return '<form method="POST" action="'.$del.'" onsubmit="return confirm(\'Delete?\')" style="display:inline-block">'
                    .csrf_field().method_field('DELETE').
                    '<button class="btn btn-sm btn-outline-danger">Delete</button></form>';
            })
            ->filter(function($query) use ($req){
                $s = $req->input('search.value');
                if(!$s) return;
                $query->where(function($qq) use ($s){
                    $qq->where('properties.vp_case_no','like',"%{$s}%")
                       ->orWhere('lessees.name','like',"%{$s}%")
                       ->orWhere('applications.type','like',"%{$s}%")
                       ->orWhere('applications.note','like',"%{$s}%");
                });
            })
            ->filterColumn('vp_case_no', function($q,$kw){
                $q->where('properties.vp_case_no','like',"%{$kw}%");
            })
            ->filterColumn('lessee_name', function($q,$kw){
                $q->where('lessees.name','like',"%{$kw}%");
            })
            ->orderColumn('vp_case_no','properties.vp_case_no $1')
            ->orderColumn('lessee_name','lessees.name $1')
            ->rawColumns(['files','actions'])
            ->toJson();
    }

    public function create(){
        return view('applications.create');
    }

    public function store(Request $req)
    {
        // date: dd/mm/yyyy → Y-m-d
        $req->merge([
            'app_date' => $this->parseDmy($req->input('app_date')),
        ]);

        $validated = $req->validate([
            'type'          => 'required|in:renewal,ownership_change',
            'property_id'   => 'required|exists:properties,id',
            'app_date'      => 'required|date',
            'note'          => 'nullable|string|max:2000',

            'application_pdf' => 'required|file|mimes:pdf|max:3072', // 3MB

            // conditional uploads
            'dcr_pdf'         => 'nullable|file|mimes:pdf|max:3072', // only for renewal
            'extra_docs.*'    => 'nullable|file|mimes:pdf|max:3072', // only for ownership change
        ]);

        // pick latest/active lease for the property (if exists)
        $lease = Lease::where('property_id', $validated['property_id'])
                      ->orderByDesc('approved_at')
                      ->orderByDesc('id')
                      ->first();

        $folder = 'applications';
        $paths = [];

        // main application
        if ($req->hasFile('application_pdf')) {
            $paths['application_pdf'] = $req->file('application_pdf')->store($folder.'/app');
        }

        // if renewal: DCR
        $dcrPath = null;
        if ($validated['type']==='renewal' && $req->hasFile('dcr_pdf')) {
            $dcrPath = $req->file('dcr_pdf')->store($folder.'/dcr');
        }

        // if ownership change: multiple supporting docs
        $extra = [];
        if ($validated['type']==='ownership_change' && $req->hasFile('extra_docs')) {
            foreach ($req->file('extra_docs') as $file) {
                $extra[] = $file->store($folder.'/extra');
            }
        }

        $app = Application::create([
            'property_id'     => $validated['property_id'],
            'lease_id'        => $lease?->id,
            'type'            => $validated['type'],
            'app_date'        => $validated['app_date'],
            'note'            => $validated['note'] ?? null,
            'application_pdf' => $paths['application_pdf'],
            'dcr_pdf'         => $dcrPath,
            'extra_docs'      => $extra ?: null,
            'created_by'      => Auth::id(),
        ]);

        return redirect()->route('applications.index')->with('ok','আবেদন যুক্ত হয়েছে।');
    }

    public function destroy(Application $application)
    {
        // optionally delete files
        try {
            if($application->application_pdf) Storage::delete($application->application_pdf);
            if($application->dcr_pdf) Storage::delete($application->dcr_pdf);
            if(is_array($application->extra_docs)){
                foreach($application->extra_docs as $p){ Storage::delete($p); }
            }
        } catch (\Throwable $e) {}
        $application->delete();

        return back()->with('ok','আবেদন মুছে ফেলা হয়েছে।');
    }

    // ------- AJAX: property dues (যখন প্রোপার্টি সিলেক্ট করবেন) -------
    public function dues(Property $property)
    {
        $lease = Lease::where('property_id',$property->id)
            ->orderByDesc('approved_at')->orderByDesc('id')->first();

        if(!$lease){
            return response()->json([
                'has_lease' => false,
                'message'   => 'এই সম্পত্তির কোনো লীজ পাওয়া যায়নি।',
            ]);
        }

        return response()->json([
            'has_lease'   => true,
            'lessee_name' => $lease->lessee?->name,
            'first_year'  => $lease->first_year,
            'last_paid_year' => $lease->last_paid_year,
            'years_due'   => $lease->years_due,      // আপনার Lease accessor ধরে
            'amount_due'  => $lease->total_due,      // আপনার Lease accessor ধরে
        ]);
    }

    // helper: dd/mm/yyyy → Y-m-d
    private function parseDmy(?string $d): ?string {
        if(!$d) return null;
        [$dd,$mm,$yy] = array_pad(explode('/',$d),3,null);
        if(!$dd || !$mm || !$yy) return null;
        return sprintf('%04d-%02d-%02d', (int)$yy, (int)$mm, (int)$dd);
    }
}
