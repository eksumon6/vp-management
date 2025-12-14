<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeasePayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lease_id','from_year','to_year','amount_paid','receipt_no','receipt_date','approved_at','scan_path'
    ];

    // 👇 Ensure dates are Carbon (so format() works), and amount is numeric
    protected $casts = [
        'receipt_date' => 'date',      // date (no time)
        'approved_at'  => 'datetime',
        'amount_paid'  => 'decimal:2',
    ];

    public function lease(){ return $this->belongsTo(Lease::class); }
}
