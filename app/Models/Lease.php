<?php
/* One more thing I have forgotten to mention. Any table used in the application should use backend datatable and form. dropdown, or select items should have bachkend ajax auto complete suggestion. */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lease extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'property_id','lessee_id','first_year','last_paid_year','annual_rate','approved_at'
    ];

    public function property(){ return $this->belongsTo(Property::class); }
    public function lessee()  { return $this->belongsTo(Lessee::class);  }
    public function payments(){ return $this->hasMany(LeasePayment::class); }
    public function notices() { return $this->hasMany(Notice::class);     }

    public function getYearsDueAttribute(): int {
        $by = app('calendar')->currentBanglaYear();
        $last = $this->last_paid_year ?? ($this->first_year - 1);
        return max(0, $by - $last);
    }

    public function getTotalDueAttribute(): float {
        return $this->years_due * (float)$this->annual_rate;
    }
}
