<?php
/* One more thing I have forgotten to mention. Any table used in the application should use backend datatable and form. dropdown, or select items should have bachkend ajax auto complete suggestion. */

namespace App\Http\Controllers;

use App\Models\Lease;
use App\Models\LeasePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentsController extends Controller
{
    public function create(Lease $lease)
    {
        $by   = app('calendar')->currentBanglaYear();
        $from = ($lease->last_paid_year ?? ($lease->first_year - 1)) + 1;
        $to   = $by; // ডিফল্ট: চলতি সন পর্যন্ত
        $years = max(0, $to - $from + 1);
        $amount = $years * (float)$lease->annual_rate;

        return view('payments.create', compact('lease','by','from','to','years','amount'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'lease_id'     =>'required|exists:leases,id',
            'from_year'    =>'required|integer|min:1300|max:1700',
            'to_year'      =>'required|integer|min:1300|max:1700|gte:from_year',
            'amount_paid'  =>'required|numeric|min:0',
            'receipt_no'   =>'nullable|string|max:100',
            'receipt_date' =>'nullable|date',
            'approved_at'  =>'nullable|date',
            'scan'         =>'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
        ]);

        $lease = Lease::findOrFail($data['lease_id']);
        $fromExpected = ($lease->last_paid_year ?? ($lease->first_year - 1)) + 1;
        if ($data['from_year'] != $fromExpected) {
            return back()->withErrors(['from_year'=>"from_year অবশ্যই {$fromExpected} হবে"])->withInput();
        }

        // যদি scan ফাইল থাকে, সেভ করুন
        $scanPath = null;
        if ($request->hasFile('scan')) {
            $scanPath = $request->file('scan')->store('lease_scans');
        }

        // পেমেন্ট তৈরি
        $payment = LeasePayment::create([
            'lease_id'    => $lease->id,
            'from_year'   => $data['from_year'],
            'to_year'     => $data['to_year'],
            'amount_paid' => $data['amount_paid'],
            'receipt_no'  => $data['receipt_no'] ?? null,
            'receipt_date'=> $data['receipt_date'] ?? null,
            'approved_at' => $data['approved_at'] ?? null,
            'scan_path'   => $scanPath,
        ]);

        // লীজ আপডেট: last_paid_year = to_year
        $lease->update(['last_paid_year'=>$data['to_year']]);

        return redirect()->route('reports.dues')->with('ok','লিজ নবায়ন/পেমেন্ট রেকর্ড করা হয়েছে।');
    }
}
