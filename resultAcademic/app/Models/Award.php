<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Award extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'name',
        'type',
        'date',
        'description'
    ];

   
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
