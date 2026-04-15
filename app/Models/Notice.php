<?php
/* One more thing I have forgotten to mention. Any table used in the application should use backend datatable and form. dropdown, or select items should have bachkend ajax auto complete suggestion. */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lease_id',
        'generated_by',
        'process_no',
        'issue_date',
        'due_year',
        'due_amount',
        'file_path',
        'generated_at',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'issue_date'   => 'date',
    ];

    public function lease(){ return $this->belongsTo(Lease::class); }
}
