<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningStrand extends Model
{
    protected $fillable = [
        'title',
        'description',
        'user_id',
        'status',
        'learning_strand_tags',
        'reapply_cooldown_days',
    ];

    protected $casts = [
        'learning_strand_tags' => 'array',
        'reapply_cooldown_days' => 'integer',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(LearningStrandApplication::class, 'learning_strand_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }
}