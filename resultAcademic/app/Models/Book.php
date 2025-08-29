<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Publication;

class Book extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'editorial',
        'place',
    ];

    public function publication(): BelongsTo
    {
        // Clave compartida: books.id = publications.id
        return $this->belongsTo(Publication::class, 'id', 'id');
    }
}
