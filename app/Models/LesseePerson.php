<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LesseePerson extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'lessee_id', 'name', 'father_name', 'nid', 'mobile', 'address',
    ];

    public function lessee()
    {
        return $this->belongsTo(Lessee::class);
    }
}
