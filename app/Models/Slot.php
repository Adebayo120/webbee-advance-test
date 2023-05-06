<?php

namespace App\Models;

use App\Filters\Slot\SlotFilters;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Slot extends Model
{
    use HasFactory;

    protected $casts = ['start_date' => 'datetime'];

    protected $appends = ['start_date_in_unix_timestamp', 'start_date_without_time_in_unix_timestamp'];

    public function bookableCalender(): BelongsTo
    {
        return $this->belongsTo(BookableCalender::class, 'bookable_calender_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function availableBookableCalenders(): BelongsTo
    {
        return $this->bookableCalender()->isAvailable();
    }

    protected function startDateInUnixTimestamp(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->start_date->timestamp,
        );
    }

    protected function startDateWithoutTimeInUnixTimestamp(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->start_date->startOfDay()->timestamp,
        );
    }

    public function scopeIsAvailable(Builder $query, int $appointmentsCountLimit): void
    {
        $query->withCount('appointments')
            ->having('appointments_count', '<', $appointmentsCountLimit);
    }

    public function scopeBelongsToAvailableBookableCalenderForService(Builder $query, Service $service): void
    {
        $query->whereHas('availableBookableCalenders', function ($calender) use($service){
            $calender->where('service_id', $service->id);
        });
    }

    public function scopeFilter(Builder $builder, array $data): Builder
    {
        return (new SlotFilters($data))->filter($builder);
    }
}
