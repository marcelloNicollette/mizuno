<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ColorSizeRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'color_id',
        'size_run_id',
        'article_label',
        'article_value',
        'me_article_gen',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    public function sizeRun(): BelongsTo
    {
        return $this->belongsTo(SizeRun::class);
    }
}
