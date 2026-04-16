<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class MbgMenu extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'sppg_provider_id',
        'menu_name',
        'description',
        'serve_date',
        'meal_type',
        'nutrition_data',
        'photo_path',
        'calories',
        'protein_g',
        'carbs_g',
        'fat_g',
    ];

    protected $casts = [
        'serve_date' => 'date',
        'nutrition_data' => 'array',
        'calories' => 'decimal:2',
        'protein_g' => 'decimal:2',
        'carbs_g' => 'decimal:2',
        'fat_g' => 'decimal:2',
    ];

    public function sppgProvider(): BelongsTo
    {
        return $this->belongsTo(SppgProvider::class, 'sppg_provider_id');
    }
}
