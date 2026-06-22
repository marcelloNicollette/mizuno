<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasAccessControl;

class Blog extends Model
{
    use HasAccessControl;

    protected $fillable = [
        'title',
        'subtitle',
        'thumb_image',
        'cover_image',
        'description',
        'content',
        'author',
        'material_date',
        'publish_at',
        'unpublish_at',
        'status',
        'active',
        'access_levels',
    ];

    protected $casts = [
        'material_date' => 'date',
        'publish_at' => 'datetime',
        'unpublish_at' => 'datetime',
        'status' => 'boolean',
        'active' => 'boolean',
        'access_levels' => 'array',
    ];
}
