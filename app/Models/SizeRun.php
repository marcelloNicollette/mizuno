<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SizeRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'title',
        'size_label_left',
        'size_label_right',
        'note',
        'sort_order',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(SizeRunItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function colorAssignments(): HasMany
    {
        return $this->hasMany(ColorSizeRun::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true)->orderBy('sort_order')->orderBy('name');
    }
}
