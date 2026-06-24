<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grave extends Model
{
    protected $fillable = [
        'full_name', 'birth_date', 'death_date', 'section',
        'plot_number', 'latitude', 'longitude', 'description', 'image_url',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'death_date' => 'date',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }
}
