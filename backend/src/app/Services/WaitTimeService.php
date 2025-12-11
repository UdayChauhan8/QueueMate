<?php

namespace App\Services;

use App\Models\Clinic;
use App\Models\Service;
use App\Models\Token;

class WaitTimeService
{
    public static function estimate(Clinic $clinic, ?Service $service): int
    {
        $serviceId = $service?->id;
        $queueAhead = Token::where('clinic_id', $clinic->id)
            ->when($serviceId, fn($q) => $q->where('service_id', $serviceId))
            ->where('status', 'waiting')
            ->count();

        $mean = $service?->avg_duration_minutes
            ?? ($clinic->settings['avg_duration_default'] ?? 10);

        $activeCounters = 1; // MVP
        $eta = (int) ceil(($queueAhead * $mean) / max($activeCounters, 1) * 1.10);
        return max($eta, 0);
    }
}
