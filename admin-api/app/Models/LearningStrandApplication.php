<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningStrandApplication extends Model
{
    protected $primaryKey = 'application_id';

    protected $fillable = [
        'learning_strand_id',
        'user_id',
        'status',
        'reapply_eligible_at',
    ];

    protected $casts = [
        'reapply_eligible_at' => 'datetime',
    ];

    public function learningStrand(): BelongsTo
    {
        return $this->belongsTo(LearningStrand::class, 'learning_strand_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}