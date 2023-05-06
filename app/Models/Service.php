<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    public function configuredBreaks(): HasMany
    {
        return $this->hasMany(ConfiguredBreak::class);
    }

    public function plannedOffs(): HasMany
    {
        return $this->hasMany(PlannedOff::class);
    }
}
