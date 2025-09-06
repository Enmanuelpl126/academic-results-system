<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Award;
use App\Models\Recognition;
use App\Models\Event;
use App\Models\Publication;
use App\Models\Department;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use HasRoles;
    /** 
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'department_id',
        'ci',
        'teaching_category',
        'scientific_category',
        'professional_level',
        'is_enabled'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_enabled' => 'boolean',
        'password' => 'hashed',
    ];

    public function awards(): BelongsToMany
    {
        return $this->belongsToMany(Award::class);
    }

    public function recognitions()
    {
        return $this->belongsToMany(Recognition::class);
    }

    public function events()
    {
        return $this->belongsToMany(Event::class);
    }

    public function publications()
    {
        return $this->belongsToMany(Publication::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }



}
