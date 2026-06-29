<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CemeteryPolygon extends Model
{
    protected $fillable = [
        'name', 'geojson', 'area_sqm', 'area_hectares', 'cemetery_id',
    ];

    protected function casts(): array
    {
        return [
            'geojson' => 'json',
            'area_sqm' => 'float',
            'area_hectares' => 'float',
        ];
    }

    public function cemetery(): BelongsTo
    {
        return $this->belongsTo(Cemetery::class);
    }
}
