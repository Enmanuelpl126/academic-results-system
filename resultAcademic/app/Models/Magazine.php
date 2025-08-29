<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Publication;

class Magazine extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'name',
        'number',
        'volume',
        'doi',
    ];

    public function publication(): BelongsTo
    {
        // Clave compartida: magazines.id = publications.id
        return $this->belongsTo(Publication::class, 'id', 'id');
    }
}
