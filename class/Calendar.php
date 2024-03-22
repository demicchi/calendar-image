<?php

namespace StudioDemmys\CalendarImage;

use Cassandra\Date;

class Calendar
{
    protected function __construct()
    {
    }
    
    public static function getMaxBaselineOffsetForDaysOfTheMonth(\DateTimeInterface $date) : int
    {
        $first_day = \DateTimeImmutable::createFromInterface($date)->modify("first day of this month");
        Logging::debug("first day: " . $first_day->format(\DateTimeInterface::ATOM));
        $last_day = \DateTimeImmutable::createFromInterface($date)->modify("last day of this month");
        Logging::debug("last day: " . $last_day->format(\DateTimeInterface::ATOM));
        $baseline_offset = 0;
        $target_day = \DateTime::createFromImmutable($first_day);
        while ($target_day <= $last_day) {
            Logging::debug("target day: " . $target_day->format(\DateTimeInterface::ATOM));
            $bounding_box = Image::calculateBoundingBox(
                Config::THIS_MONTH_DAYS_FONT_SIZE,
                Config::THIS_MONTH_DAYS_FONT_FILE,
                $target_day->format("j")
            );
            $baseline_offset = max($baseline_offset, $bounding_box->baseline_offset);
            Logging::debug("the baseline offset of this day is " . $baseline_offset);
            $target_day->modify("+1 day");
        }
        Logging::debug("The maximum baseline offset for the days of this month is ". $baseline_offset);
        return $baseline_offset;
    }
    
    public static function getTextColorOfTheDay(\GdImage $image, \DateTimeInterface $target_day, array $color_set) : int
    {
        if ($target_day->format("w") == "0") {
            $text_color = imagecolorallocatealpha(
                $image,
                $color_set["sunday"][0],
                $color_set["sunday"][1],
                $color_set["sunday"][2],
                $color_set["sunday"][3]
            );
        } elseif ($target_day->format("w") == "6") {
            $text_color = imagecolorallocatealpha(
                $image,
                $color_set["saturday"][0],
                $color_set["saturday"][1],
                $color_set["saturday"][2],
                $color_set["saturday"][3]
            );
        } else {
            $text_color = imagecolorallocatealpha(
                $image,
                $color_set["weekday"][0],
                $color_set["weekday"][1],
                $color_set["weekday"][2],
                $color_set["weekday"][3]
            );
        }
        if ($text_color === false)
            exit("error");
        return $text_color;
    }
    
    public static function renderDaysCalendar(\GdImage $image, \DateTimeInterface $date,
                                              array $days_box_set, float $days_font_size, string $days_font_file,
                                              array $color_set, string $date_format,
                                              HolidaysOfTheYear $holidays, ?array $holidays_box_set = null,
                                              ?float $holidays_font_size = null, ?string $holidays_font_file = null,
                                              ?array $target_date_box_set = null,
                                              array|int|null $target_date_background_color = null,
                                              array|int|null $target_date_line_color = null): void
    {
        $first_day = \DateTimeImmutable::createFromInterface($date)->modify("first day of this month");
        Logging::debug("first day: " . $first_day->format(\DateTimeInterface::ATOM));
        $last_day = \DateTimeImmutable::createFromInterface($date)->modify("last day of this month");
        Logging::debug("last day: " . $last_day->format(\DateTimeInterface::ATOM));
        
        $first_box = (intval($first_day->format("w")) + 7 - Config::FIRST_WDAY) % 7;
        
        Logging::debug("Calculate the baseline offset for the days in this month");
        $baseline_offset = self::getMaxBaselineOffsetForDaysOfTheMonth($date);
        
        Logging::debug("Write the days of this month");
        $target_day = \DateTime::createFromImmutable($first_day);
        $target_box = $first_box;
        while ($target_day <= $last_day) {
            Logging::debug("target day: " . $target_day->format(\DateTimeInterface::ATOM));
            
            if (intval($target_day->diff($date)->format("%a")) == 0) {
                if (!is_null($target_date_box_set)) {
                    Image::illuminateBox(
                        $image,
                        $target_date_box_set[$target_box]["position"][0],
                        $target_date_box_set[$target_box]["position"][1],
                        $target_date_box_set[$target_box]["size"][0],
                        $target_date_box_set[$target_box]["size"][1],
                        $target_date_background_color,
                        $target_date_line_color,
                        $target_date_box_set[$target_box]["shape"]
                    );
                }
            }
            
            $holiday = $holidays->getHoliday($target_day);
            if (is_null($holiday)) {
                $text_color = self::getTextColorOfTheDay($image, $target_day, $color_set);
            } else {
                $text_color = imagecolorallocatealpha(
                    $image,
                    $holiday->color->r,
                    $holiday->color->g,
                    $holiday->color->b,
                    $holiday->color->a,
                );
                
                if (!is_null($holidays_box_set)) {
                    $text_box = Image::writeTextInBox(
                        $image,
                        $holidays_box_set[$target_box]["position"],
                        $holidays_box_set[$target_box]["size"],
                        $holidays_font_size ?? Config::THIS_MONTH_HOLIDAYS_FONT_FILE,
                        $holidays_font_file ?? Config::THIS_MONTH_HOLIDAYS_FONT_SIZE,
                        $text_color,
                        $holiday->name ?? Config::DEFAULT_HOLIDAY_NAME
                    );
                    
                    if ($text_box === false)
                        exit("error");
                }
            }
            
            $text_box = Image::writeTextInBox(
                $image,
                $days_box_set[$target_box]["position"],
                $days_box_set[$target_box]["size"],
                $days_font_size,
                $days_font_file,
                $text_color,
                $target_day->format($date_format),
                $baseline_offset
            );
            
            if ($text_box === false)
                exit("error");
            
            $target_box++;
            $target_day->modify("+1 day");
        }
    }
    
}