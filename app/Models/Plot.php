<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'plot_number', 'section', 'lat', 'lng', 'shape',
    'lot_type', 'dimension', 'capacity',
    'current_occupants', 'status', 'price', 'notes',
])]
class Plot extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'lat' => 'float',
            'lng' => 'float',
            'shape' => 'array',
            'capacity' => 'integer',
            'current_occupants' => 'integer',
            'price' => 'float',
        ];
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function burials(): HasMany
    {
        return $this->hasMany(Burial::class);
    }
}
