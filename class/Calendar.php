<?php

namespace StudioDemmys\CalendarImage;

class Calendar
{
    protected function __construct()
    {
    }
    
    public static function getMaxBaselineOffsetForDaysOfTheMonth(\DateTimeInterface $date, float $font_size,
                                                                 string $font_file, string $format,
                                                                 ?HolidaysOfTheYear $holidays = null,
                                                                 ?string $locale = null, ?string $timezone = null) : int
    {
        $first_day = \DateTimeImmutable::createFromInterface($date)->modify("first day of this month");
        Logging::debug("first day: " . $first_day->format(\DateTimeInterface::ATOM));
        $last_day = \DateTimeImmutable::createFromInterface($date)->modify("last day of this month");
        Logging::debug("last day: " . $last_day->format(\DateTimeInterface::ATOM));
        $baseline_offset = 0;
        $target_day = \DateTime::createFromImmutable($first_day);
        while ($target_day <= $last_day) {
            Logging::debug("target day: " . $target_day->format(\DateTimeInterface::ATOM));
            $print_text = Calendar::getPrintText($target_day, $format, $holidays, $locale, $timezone);
            $bounding_box = Image::calculateBoundingBox($font_size, $font_file, $print_text);
            $baseline_offset = max($baseline_offset, $bounding_box->baseline_offset);
            Logging::debug("the baseline offset of this day is " . $baseline_offset);
            $target_day->modify("+1 day");
        }
        Logging::debug("The maximum baseline offset for the days of this month is ". $baseline_offset);
        return $baseline_offset;
    }
    
    public static function getPrintText(\DateTimeInterface $target_date, string $format,
                                        ?HolidaysOfTheYear $holidays = null, ?string $locale = null,
                                        ?string $timezone = null) : string
    {
        if ($format == "holiday_name" && !is_null($holidays)) {
            $holiday = $holidays->getHoliday($target_date);
            $print_text = $holiday->name ?? "";
        } else {
            if (is_null($locale)) {
                $print_text = $target_date->format($format);
            } else {
                $formatter = new \IntlDateFormatter(
                    $locale,
                    \IntlDateFormatter::NONE,
                    \IntlDateFormatter::NONE,
                    $timezone,
                    \IntlDateFormatter::TRADITIONAL
                );
                $formatter->setPattern($format);
                $print_text = $formatter->format($target_date);
            }
        }
        return $print_text;
    }
    
    public static function getTextColorOfTheDay(\GdImage $image, \DateTimeInterface $target_date,
                                                array $color_set = [], array $default_color = [0, 0, 0, 0]) : int
    {
        $day_of_week = strtolower($target_date->format("l"));
        
        $print_color = $color_set[$day_of_week] ?? $default_color;
        $text_color = imagecolorallocatealpha(
            $image,
            $print_color[0] ?? 0,
            $print_color[1] ?? 0,
            $print_color[2] ?? 0,
            $print_color[3] ?? 0
        );
        
        if ($text_color === false)
            exit("error");
        return $text_color;
    }
    
    public static function getTextColorOfHoliday(\GdImage $image, \DateTimeInterface $target_date,
                                                 HolidaysOfTheYear $holidays) : ?int
    {
        $holiday = $holidays->getHoliday($target_date);
        $holiday_color = null;
        if (!is_null($holiday)) {
            $holiday_color = imagecolorallocatealpha(
                $image,
                $holiday->color->r,
                $holiday->color->g,
                $holiday->color->b,
                $holiday->color->a,
            );
        }
        return $holiday_color;
    }
    
    public static function renderDaysCalendar(\GdImage $image, \DateTimeInterface $date, int $first_wday,
                                              array $days_box_set, float $days_font_size, string $days_font_file,
                                              array $color_set, array $default_color, bool $holiday_color,
                                              string $date_format, HolidaysOfTheYear $holidays, ?string $locale = null,
                                              ?string $timezone = null, ?array $illumination_box_set = null) : void
    {
        $first_day = \DateTimeImmutable::createFromInterface($date)->modify("first day of this month");
        Logging::debug("first day: " . $first_day->format(\DateTimeInterface::ATOM));
        $last_day = \DateTimeImmutable::createFromInterface($date)->modify("last day of this month");
        Logging::debug("last day: " . $last_day->format(\DateTimeInterface::ATOM));
        
        $first_box = (intval($first_day->format("w")) + 7 - $first_wday) % 7;
        
        Logging::debug("Calculate the baseline offset for the days in this month");
        $baseline_offset = self::getMaxBaselineOffsetForDaysOfTheMonth($date, $days_font_size, $days_font_file,
            $date_format, $holidays, $locale, $timezone);
        
        Logging::debug("Write the days of this month");
        $target_day = \DateTime::createFromImmutable($first_day);
        $target_box = $first_box;
        while ($target_day <= $last_day) {
            Logging::debug("target day: " . $target_day->format(\DateTimeInterface::ATOM));
            
            if (!is_null($illumination_box_set)) {
                if (intval($target_day->diff($date)->format("%a")) == 0) {
                    Image::illuminateBox(
                        $image,
                        $illumination_box_set[$target_box]["position"][0],
                        $illumination_box_set[$target_box]["position"][1],
                        $illumination_box_set[$target_box]["size"][0],
                        $illumination_box_set[$target_box]["size"][1],
                        $illumination_box_set[$target_box]["background_color"],
                        $illumination_box_set[$target_box]["line_color"],
                        $illumination_box_set[$target_box]["shape"]
                    );
                }
            }
            
            $print_text = Calendar::getPrintText($target_day, $date_format, $holidays, $locale, $timezone);
            
            $text_color = self::getTextColorOfTheDay($image, $target_day, $color_set, $default_color);
            if (!$text_color)
                $text_color = [0, 0, 0, 0];
            
            if ($holiday_color)
                $text_color = Calendar::getTextColorOfHoliday($image, $target_day, $holidays) ?? $text_color;
            
            $text_box = Image::writeTextInBox(
                $image,
                $days_box_set[$target_box]["position"],
                $days_box_set[$target_box]["size"],
                $days_font_size,
                $days_font_file,
                $text_color,
                $print_text,
                $baseline_offset
            );
            
            if ($text_box === false)
                exit("error");
            
            $target_box++;
            $target_day->modify("+1 day");
        }
    }
    
    /**
     * @param \DateTimeInterface[] $datetime_list
     */
    public static function getNextDateTimeIndex(\DateTimeInterface $needle, array $datetime_list)
    : ?int
    {
        $reference_timestamp = $needle->getTimestamp();
        asort($datetime_list);
        foreach ($datetime_list as $key => $datetime) {
            $diff = $datetime->getTimestamp() - $reference_timestamp;
            if ($diff > 0)
                return $key;
        }
        return null;
    }
    
    /**
     * @param string[] $datetime_list
     * @return \DateTimeImmutable[]
     */
    public static function getDateTimeImmutableFromStringForEach(\DateTimeInterface $now, array $datetime_list): array
    {
        return array_map(
            fn($value): \DateTimeImmutable => \DateTimeImmutable::createFromInterface($now)->modify($value),
            $datetime_list
        );
    }
    
    /**
     * @param \DateTimeInterface[] $datetime_list
     * @return \DateTimeImmutable[]
     */
    public static function getNextDayForEach(array $datetime_list): array
    {
        return array_map(
            fn($value): \DateTimeImmutable => \DateTimeImmutable::createFromInterface($value)->modify("+1 day"),
            $datetime_list
        );
    }
}