<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lessee extends Model
{
    use HasFactory, SoftDeletes;

    // পুরোনো ফিল্ডগুলো রেখেছি (ব্যাকওয়ার্ড কম্প্যাটিবল)
    protected $fillable = ['name','father_name','nid','mobile','address'];

    public function leases()
    {
        return $this->hasMany(Lease::class);
    }

    public function persons()
    {
        return $this->hasMany(LesseePerson::class);
    }

    // সুবিধার জন্য: প্রথম ব্যক্তির নাম দেখাতে
    public function getPrimaryNameAttribute(): ?string
    {
        $p = $this->persons()->orderBy('id')->first();
        return $p?->name ?: $this->name;
    }
}
