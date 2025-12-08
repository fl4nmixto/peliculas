<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovieSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'movie_id',
        'provider_id',
        'url',
        'quality',
    ];

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
