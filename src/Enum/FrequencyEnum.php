<?php

namespace NabilHassen\LaravelUsageLimiter\Enum;

use Carbon\Carbon;

enum FrequencyEnum: string
{
    case EVERY_SECOND = 'every second';
    case EVERY_MINUTE = 'every minute';
    case EVERY_HOUR  = 'every hour';
    case EVERY_DAY  = 'every day';
    case EVERY_WEEK  = 'every week';
    case EVERY_TWO_WEEKS  = 'every two weeks';
    case EVERY_MONTH  = 'every month';
    case EVERY_QUARTER  = 'every quarter';
    case EVERY_SIX_MONTHS  = 'every six months';
    case EVERY_YEAR  = 'every year';

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getCarbonEquivalent(string|Carbon $lastReset): Carbon
    {
        $lastReset = Carbon::parse($lastReset);

        return match ($this) {
            self::EVERY_SECOND => $lastReset->addSecond(),
            self::EVERY_MINUTE => $lastReset->addMinute(),
            self::EVERY_HOUR => $lastReset->addHour(),
            self::EVERY_DAY => $lastReset->addDay(),
            self::EVERY_WEEK => $lastReset->addWeek(),
            self::EVERY_TWO_WEEKS => $lastReset->addWeeks(2),
            self::EVERY_MONTH => $lastReset->addMonth(),
            self::EVERY_QUARTER => $lastReset->addQuarter(),
            self::EVERY_SIX_MONTHS => $lastReset->addMonths(6),
            self::EVERY_YEAR => $lastReset->addYear(),
        };
    }
}