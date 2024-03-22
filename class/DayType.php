<?php

namespace StudioDemmys\CalendarImage;

enum DayType: string
{
    case Weekday = "weekday";
    case Saturday = "saturday";
    case Sunday = "sunday";
    case NationalHoliday = "national_holiday";
    case WorkHoliday = "work_holiday";
    case SubstituteHoliday = "substitute_holiday";
    case SandwichHoliday = "sandwich_holiday";
    
    public static function getDayType(string $day_type): ?self
    {
        foreach (self::cases() as $case) {
            if (strtolower($day_type) == strtolower($case->name))
                return $case;
        }
        return null;
    }
}
