<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PossibleIllness extends Model
{
    protected $fillable = ['name'];
    
    /**
     * The symptoms that belong to the possible illness.
     */
    public function symptoms(): BelongsToMany
    {
        return $this->belongsToMany(Symptom::class, 'symptom_illness')
                    ->withTimestamps();
    }
    
    /**
     * Scope a query to search illnesses by name.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', '%' . $search . '%');
    }
    
    /**
     * Get the matching symptoms count for a given set of symptom IDs.
     */
    public function getMatchingSymptomsCount(array $symptomIds): int
    {
        return $this->symptoms()->whereIn('symptoms.id', $symptomIds)->count();
    }
    
    /**
     * Get the matching percentage for a given set of symptom IDs.
     */
    public function getMatchingPercentage(array $symptomIds): float
    {
        $totalSymptoms = $this->symptoms()->count();
        if ($totalSymptoms === 0) return 0;
        
        $matchingSymptoms = $this->getMatchingSymptomsCount($symptomIds);
        return ($matchingSymptoms / $totalSymptoms) * 100;
    }
}