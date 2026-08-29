<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'interests',
        'favorite_topics',
        'preferred_categories',
        'skills',
        'learning_goals',
        'profile_embedding',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'interests' => 'array',
        'favorite_topics' => 'array',
        'preferred_categories' => 'array',
        'skills' => 'array',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Build a single text blob from the profile fields, used to
     * generate the embedding for recommendations.
     */
    public function profileText(): string
    {
        return collect([
            'Interests: ' . implode(', ', $this->interests ?? []),
            'Favorite topics: ' . implode(', ', $this->favorite_topics ?? []),
            'Preferred categories: ' . implode(', ', $this->preferred_categories ?? []),
            'Skills: ' . implode(', ', $this->skills ?? []),
            'Learning goals: ' . ($this->learning_goals ?? ''),
        ])->implode('. ');
    }

    public function chatLogs()
    {
        return $this->hasMany(ChatLog::class);
    }
}
