<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SizeRunItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'size_run_id',
        'left_value',
        'right_value',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function sizeRun(): BelongsTo
    {
        return $this->belongsTo(SizeRun::class);
    }
}
