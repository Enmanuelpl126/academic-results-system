<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Magazine;
use App\Models\Book;
use App\Models\Chapter;

class Publication extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'name',
        'type',
        'date',
        'description'
    ];



    public function magazine(): HasOne
    {
        // Relación 1:1 con clave compartida (magazines.id = publications.id)
        return $this->hasOne(Magazine::class, 'id', 'id');
    }

    /**
     * Get the book record associated with the publication.
     */
    public function book(): HasOne
    {
        // Relación 1:1 con clave compartida (books.id = publications.id)
        return $this->hasOne(Book::class, 'id', 'id');
    }

    /**
     * Get the chapter record associated with the publication.
     */
    public function chapter(): HasOne
    {
        // Relación 1:1 con clave compartida (chapters.id = publications.id)
        return $this->hasOne(Chapter::class, 'id', 'id');
    }

    /**
     * Get the users associated with the publication.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
