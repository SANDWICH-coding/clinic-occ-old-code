<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Symptom extends Model
{
    protected $fillable = ['name'];
    
    /**
     * The possible illnesses that belong to the symptom.
     */
    public function possibleIllnesses(): BelongsToMany
    {
        return $this->belongsToMany(PossibleIllness::class, 'symptom_illness')
                    ->withTimestamps();
    }
    
    /**
     * Scope a query to search symptoms by name.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', '%' . $search . '%');
    }
}