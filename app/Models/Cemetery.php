<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cemetery extends Model
{
    protected $fillable = [
        'name', 'description', 'address', 'lat', 'lng', 'entrance_node_id',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'float',
            'lng' => 'float',
        ];
    }

    public function polygon(): HasOne
    {
        return $this->hasOne(CemeteryPolygon::class, 'cemetery_id');
    }

    public function entranceNode(): BelongsTo
    {
        return $this->belongsTo(PathNode::class, 'entrance_node_id');
    }
}
