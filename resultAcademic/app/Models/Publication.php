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
        return $this->hasOne(Magazine::class);
    }

    /**
     * Get the book record associated with the publication.
     */
    public function book(): HasOne
    {
        return $this->hasOne(Book::class);
    }

    /**
     * Get the chapter record associated with the publication.
     */
    public function chapter(): HasOne
    {
        return $this->hasOne(Chapter::class);
    }

    /**
     * Get the users associated with the publication.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
