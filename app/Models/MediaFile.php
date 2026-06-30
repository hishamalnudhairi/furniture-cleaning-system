<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MediaFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'mediable_type',
        'mediable_id',
        'collection',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }
}
