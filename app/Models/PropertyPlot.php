<?php
/* One more thing I have forgotten to mention. Any table used in the application should use backend datatable and form. dropdown, or select items should have bachkend ajax auto complete suggestion. */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyPlot extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'property_id',
        'dag_no',
        'land_class',
        'area_value',
        'area_unit',
        'annual_rate', // প্রতি দাগের বার্ষিক রেট
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
