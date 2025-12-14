<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'property_id','lease_id','type','app_date','note',
        'application_pdf','dcr_pdf','extra_docs','created_by'
    ];

    protected $casts = [
        'app_date'   => 'date',
        'extra_docs' => 'array',
    ];

    public function property(){ return $this->belongsTo(Property::class); }
    public function lease(){ return $this->belongsTo(Lease::class); }

    // শো করার সময় বাংলা/ইউজার-ফ্রেন্ডলি লেবেল
    public function getTypeLabelAttribute(): string {
        return $this->type === 'renewal' ? 'লীজ নবায়নের আবেদন' : 'মালিকানা পরিবর্তনের আবেদন';
    }
}
