<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Lease;
use App\Models\LeasePayment;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Bangladesh timezone
        $now = now('Asia/Dhaka');

        // Fiscal year: Jul 1 (current year if month>=7 else previous year) → next year's Jun 30 23:59:59
        $fyStart = ($now->month >= 7)
            ? Carbon::create($now->year, 7, 1, 0, 0, 0, 'Asia/Dhaka')
            : Carbon::create($now->year - 1, 7, 1, 0, 0, 0, 'Asia/Dhaka');

        $fyEnd = (clone $fyStart)->addYear()->subSecond(); // up to Jun 30, 23:59:59

        // 1) মোট ভিপি প্রোপার্টি
        $totalProperties = Property::count();

        // 2) লীজের আওতায় থাকা ভিপির সংখ্যা (distinct property_id in leases)
        // If you have an "active" flag, add ->where('status','active') here.
        $leasedPropertyCount = Lease::distinct('property_id')->count('property_id');

        // 3) চলতি অর্থবছরে মোট আদায় (LeasePayment)
        // Prefer receipt_date; if null, fallback to created_at
        $fyCollection = (float) LeasePayment::query()
            ->where(function ($q) use ($fyStart, $fyEnd) {
                $q->whereBetween('receipt_date', [$fyStart->toDateString(), $fyEnd->toDateString()])
                  ->orWhere(function ($qq) use ($fyStart, $fyEnd) {
                      $qq->whereNull('receipt_date')
                         ->whereBetween('created_at', [$fyStart, $fyEnd]);
                  });
            })
            ->sum('amount_paid');

        // Labels for view
        $fyLabelStart = $fyStart->toDateString();
        $fyLabelEnd   = $fyEnd->toDateString();

        return view('dashboard.index', compact(
            'totalProperties',
            'leasedPropertyCount',
            'fyCollection',
            'fyLabelStart',
            'fyLabelEnd'
        ));
    }
}
