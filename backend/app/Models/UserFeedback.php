<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\AppendOnly;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class UserFeedback extends Model
{
    use AppendOnly, HasFactory, HasUuid;

    protected $table = 'user_feedbacks';

    const UPDATED_AT = null;

    protected $fillable = [
        'school_id',
        'mbg_menu_id',
        'feedback_date',
        'zkp_identity_hash',
        'zkp_proof',
        'rating',
        'taste_rating',
        'portion_rating',
        'comment',
        'photo_path',
        'reporter_latitude',
        'reporter_longitude',
    ];

    protected $casts = [
        'feedback_date' => 'date',
        'rating' => 'integer',
        'reporter_latitude' => 'decimal:7',
        'reporter_longitude' => 'decimal:7',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(MbgMenu::class, 'mbg_menu_id');
    }
}
