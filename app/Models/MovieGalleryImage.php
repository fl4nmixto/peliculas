<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovieGalleryImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'movie_id',
        'image_url',
        'position',
    ];

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }
}
