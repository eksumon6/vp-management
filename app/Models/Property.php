<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'vp_case_no','union','mouza','khatian_no','jl_no','gazette_no','remarks'
    ];

    /** দাগ/প্লট সম্পর্ক */
    public function plots()
    {
        return $this->hasMany(PropertyPlot::class);
    }

    /** ➕ নতুন: এই সম্পত্তির সব লিজ */
    public function leases()
    {
        // assumes leases table has a property_id FK
        return $this->hasMany(Lease::class);
    }

    /** মোট বার্ষিক রেট (সব প্লটের যোগফল) */
    public function getTotalAnnualRateAttribute(): float
    {
        return (float) $this->plots()->sum('annual_rate');
    }

    /** ল্যান্ড ক্লাসের সারমর্ম (কমা-সেপারেটেড ইউনিক) */
    public function getClassSummaryAttribute(): string
    {
        $classes = $this->plots()
            ->pluck('land_class')
            ->filter()
            ->unique()
            ->values()
            ->all();

        return implode(', ', $classes);
    }
}
