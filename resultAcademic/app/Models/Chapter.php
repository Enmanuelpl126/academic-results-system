<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Publication;

class Chapter extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'book_name',
        'author',
        'editorial',
        'place',
    ];

    public function publication(): BelongsTo
    {
        // Clave compartida: chapters.id = publications.id
        return $this->belongsTo(Publication::class, 'id', 'id');
    }
}
