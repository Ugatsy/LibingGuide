<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'from_node_id', 'to_node_id', 'weight', 'path_type', 'is_bidirectional',
])]
class PathEdge extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'weight' => 'float',
            'is_bidirectional' => 'boolean',
        ];
    }

    public function fromNode(): BelongsTo
    {
        return $this->belongsTo(PathNode::class, 'from_node_id');
    }

    public function toNode(): BelongsTo
    {
        return $this->belongsTo(PathNode::class, 'to_node_id');
    }
}
