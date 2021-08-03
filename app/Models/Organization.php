<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'logo',
        'address',
        'phone',
    ];


    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
