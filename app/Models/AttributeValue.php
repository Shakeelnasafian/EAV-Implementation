<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AttributeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'attribute_id',
        'entity_id',
        'value',
    ];

    /**
     * The attribute that this value references.
     */
    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    /**
     * The project (entity) that this value belongs to.
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'entity_id');
    }
}
