<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    protected $fillable = [
        'learning_strand_id',
        'title',
        'type',
        'deadline',
    ];

    protected $casts = [
        'deadline' => 'datetime',
    ];

    public function learningStrand(): BelongsTo
    {
        return $this->belongsTo(LearningStrand::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}