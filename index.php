<?php

namespace StudioDemmys\CalendarImage;

if (!defined('CALENDARIMAGE_UNIQUE_ID'))
    define("CALENDARIMAGE_UNIQUE_ID", bin2hex(random_bytes(4)));

require_once dirname(__FILE__)."/class/Common.php";
require_once dirname(__FILE__)."/class/Config.php";
require_once dirname(__FILE__)."/class/ErrorLevel.php";
require_once dirname(__FILE__)."/class/Logging.php";

require_once dirname(__FILE__)."/class/Color.php";
require_once dirname(__FILE__)."/class/TextBoundingBox.php";
require_once dirname(__FILE__)."/class/DayType.php";
require_once dirname(__FILE__)."/class/Holiday.php";
require_once dirname(__FILE__)."/class/HolidaySubstitution.php";
require_once dirname(__FILE__)."/class/HolidaysOfTheYear.php";
require_once dirname(__FILE__)."/lib/gd-indexed-color-converter/GDIndexedColorConverter.php";
require_once dirname(__FILE__)."/class/Image.php";
require_once dirname(__FILE__)."/class/Calendar.php";


$now = new \DateTimeImmutable("now", new \DateTimeZone(Config::TIMEZONE));
Logging::debug("now: " . $now->format(\DateTimeInterface::ATOM));

$dither = null;
$pack_bits = null;

if (isset($_GET["mode"])) {
    $mode = strtolower($_GET["mode"]);
    switch ($mode) {
        case "timer":
            // echo seconds to 00:00 next day
            $timestamp_now = $now->getTimestamp();
            $timestamp_tomorrow = $now->modify("tomorrow")->getTimestamp();
            echo $timestamp_tomorrow - $timestamp_now;
            exit();
            break;
        case "7color4bit":
            $dither = "7color";
            $pack_bits = 4;
            break;
        case "7color":
            $dither = "7color";
            break;
    }
}



if (isset($_GET["date"])) {
    $query_date = strtolower($_GET["date"]);
    // set $now when valid yyyymmdd is passed via GET
    if (preg_match('/^\d{8}$/', $query_date) === 1) {
        try {
            $target_date = new \DateTimeImmutable($query_date, new \DateTimeZone(Config::TIMEZONE));
        } catch (\Exception) {
            $target_date = $now;
        }
        $now = $target_date;
    }
}


$image = imagecreatefromstring(file_get_contents(Config::BACKGROUND_IMAGE));
if ($image === false)
    exit("error");


// --------------------------------------------------------------------------------
// This year in western style
// --------------------------------------------------------------------------------

Logging::debug("Write this year in western style");
$text_box = Image::writeTextInBox(
    $image,
    Config::THIS_YEAR_IN_WESTERN_BOX["position"],
    Config::THIS_YEAR_IN_WESTERN_BOX["size"],
    Config::THIS_YEAR_IN_WESTERN_FONT_SIZE,
    Config::THIS_YEAR_IN_WESTERN_FONT_FILE,
    Config::THIS_YEAR_IN_WESTERN_FONT_COLOR,
    $now->format(Config::THIS_YEAR_IN_WESTERN_DATE_FORMAT)
);

if ($text_box === false)
    exit("error");

// --------------------------------------------------------------------------------
// This year in local style
// --------------------------------------------------------------------------------

Logging::debug("Write this year in local style");
$formatter = new \IntlDateFormatter(
    Config::CALENDAR_LOCALE,
    \IntlDateFormatter::NONE,
    \IntlDateFormatter::NONE,
    Config::TIMEZONE,
    \IntlDateFormatter::TRADITIONAL
);
$formatter->setPattern(Config::THIS_YEAR_IN_LOCAL_DATE_FORMAT);
$text_box = Image::writeTextInBox(
    $image,
    Config::THIS_YEAR_IN_LOCAL_BOX["position"],
    Config::THIS_YEAR_IN_LOCAL_BOX["size"],
    Config::THIS_YEAR_IN_LOCAL_FONT_SIZE,
    Config::THIS_YEAR_IN_LOCAL_FONT_FILE,
    Config::THIS_YEAR_IN_LOCAL_FONT_COLOR,
    $formatter->format($now)
);

if ($text_box === false)
    exit("error");

// --------------------------------------------------------------------------------
// This month in numerical
// --------------------------------------------------------------------------------

Logging::debug("Write this month in numerical");
$text_box = Image::writeTextInBox(
    $image,
    Config::THIS_MONTH_IN_NUMERICAL_BOX["position"],
    Config::THIS_MONTH_IN_NUMERICAL_BOX["size"],
    Config::THIS_MONTH_IN_NUMERICAL_FONT_SIZE,
    Config::THIS_MONTH_IN_NUMERICAL_FONT_FILE,
    Config::THIS_MONTH_IN_NUMERICAL_FONT_COLOR,
    $now->format(Config::THIS_MONTH_IN_NUMERICAL_DATE_FORMAT)
);

if ($text_box === false)
    exit("error");

// --------------------------------------------------------------------------------
// This month in textual
// --------------------------------------------------------------------------------

Logging::debug("Write this month in textual");
$text_box = Image::writeTextInBox(
    $image,
    Config::THIS_MONTH_IN_TEXTUAL_BOX["position"],
    Config::THIS_MONTH_IN_TEXTUAL_BOX["size"],
    Config::THIS_MONTH_IN_TEXTUAL_FONT_SIZE,
    Config::THIS_MONTH_IN_TEXTUAL_FONT_FILE,
    Config::THIS_MONTH_IN_TEXTUAL_FONT_COLOR,
    $now->format(Config::THIS_MONTH_IN_TEXTUAL_DATE_FORMAT)
);

if ($text_box === false)
    exit("error");

// --------------------------------------------------------------------------------
// Calendar of this month
// --------------------------------------------------------------------------------

Logging::debug("Render a calendar of this month");
$holidays_this_month = new HolidaysOfTheYear($now, true);
Calendar::renderDaysCalendar(
    $image,
    $now,
    Config::THIS_MONTH_DAYS_BOX_SET,
    Config::THIS_MONTH_DAYS_FONT_SIZE,
    Config::THIS_MONTH_DAYS_FONT_FILE,
    Config::THIS_MONTH_DAYS_FONT_COLOR_SET,
    Config::THIS_MONTH_DAYS_DATE_FORMAT,
    $holidays_this_month,
    Config::THIS_MONTH_HOLIDAYS_BOX_SET,
    Config::THIS_MONTH_HOLIDAYS_FONT_SIZE,
    Config::THIS_MONTH_HOLIDAYS_FONT_FILE,
    Config::THIS_MONTH_TARGET_DAY_BOX_SET,
    Config::THIS_MONTH_DAYS_BACKGROUND_COLOR,
    Config::THIS_MONTH_DAYS_LINE_COLOR
);

// --------------------------------------------------------------------------------
// Calendar of next month
// --------------------------------------------------------------------------------

$next_month = $now->modify("first day of next month");
Logging::debug("next month: " . $next_month->format(\DateTimeInterface::ATOM));

Logging::debug("Write next month in numerical");
$text_box = Image::writeTextInBox(
    $image,
    Config::NEXT_MONTH_IN_NUMERICAL_BOX["position"],
    Config::NEXT_MONTH_IN_NUMERICAL_BOX["size"],
    Config::NEXT_MONTH_IN_NUMERICAL_FONT_SIZE,
    Config::NEXT_MONTH_IN_NUMERICAL_FONT_FILE,
    Config::NEXT_MONTH_IN_NUMERICAL_FONT_COLOR,
    $next_month->format(Config::NEXT_MONTH_IN_NUMERICAL_DATE_FORMAT)
);

if ($text_box === false)
    exit("error");


Logging::debug("Write next month in textual");
$text_box = Image::writeTextInBox(
    $image,
    Config::NEXT_MONTH_IN_TEXTUAL_BOX["position"],
    Config::NEXT_MONTH_IN_TEXTUAL_BOX["size"],
    Config::NEXT_MONTH_IN_TEXTUAL_FONT_SIZE,
    Config::NEXT_MONTH_IN_TEXTUAL_FONT_FILE,
    Config::NEXT_MONTH_IN_TEXTUAL_FONT_COLOR,
    $next_month->format(Config::NEXT_MONTH_IN_TEXTUAL_DATE_FORMAT)
);

if ($text_box === false)
    exit("error");


Logging::debug("Render a calendar of next month");
if ($next_month->format("Y") == $now->format("Y")) {
    $holidays_next_month = $holidays_this_month;
} else {
    $holidays_next_month = new HolidaysOfTheYear($next_month, true);
}
Calendar::renderDaysCalendar(
    $image,
    $next_month,
    Config::NEXT_MONTH_DAYS_BOX_SET,
    Config::NEXT_MONTH_DAYS_FONT_SIZE,
    Config::NEXT_MONTH_DAYS_FONT_FILE,
    Config::NEXT_MONTH_DAYS_FONT_COLOR_SET,
    Config::NEXT_MONTH_DAYS_DATE_FORMAT,
    $holidays_next_month
);


// --------------------------------------------------------------------------------
// Calendar of previous month
// --------------------------------------------------------------------------------

$previous_month = $now->modify("first day of previous month");
Logging::debug("previous month: " . $previous_month->format(\DateTimeInterface::ATOM));

Logging::debug("Write previous month in numerical");
$text_box = Image::writeTextInBox(
    $image,
    Config::PREVIOUS_MONTH_IN_NUMERICAL_BOX["position"],
    Config::PREVIOUS_MONTH_IN_NUMERICAL_BOX["size"],
    Config::PREVIOUS_MONTH_IN_NUMERICAL_FONT_SIZE,
    Config::PREVIOUS_MONTH_IN_NUMERICAL_FONT_FILE,
    Config::PREVIOUS_MONTH_IN_NUMERICAL_FONT_COLOR,
    $previous_month->format(Config::PREVIOUS_MONTH_IN_NUMERICAL_DATE_FORMAT)
);

if ($text_box === false)
    exit("error");


Logging::debug("Write previous month in textual");
$text_box = Image::writeTextInBox(
    $image,
    Config::PREVIOUS_MONTH_IN_TEXTUAL_BOX["position"],
    Config::PREVIOUS_MONTH_IN_TEXTUAL_BOX["size"],
    Config::PREVIOUS_MONTH_IN_TEXTUAL_FONT_SIZE,
    Config::PREVIOUS_MONTH_IN_TEXTUAL_FONT_FILE,
    Config::PREVIOUS_MONTH_IN_TEXTUAL_FONT_COLOR,
    $previous_month->format(Config::PREVIOUS_MONTH_IN_TEXTUAL_DATE_FORMAT)
);

if ($text_box === false)
    exit("error");

Logging::debug("Render a calendar of previous month");
if ($previous_month->format("Y") == $now->format("Y")) {
    $holidays_previous_month = $holidays_this_month;
} else {
    $holidays_previous_month = new HolidaysOfTheYear($previous_month, true);
}
Calendar::renderDaysCalendar(
    $image,
    $previous_month,
    Config::PREVIOUS_MONTH_DAYS_BOX_SET,
    Config::PREVIOUS_MONTH_DAYS_FONT_SIZE,
    Config::PREVIOUS_MONTH_DAYS_FONT_FILE,
    Config::PREVIOUS_MONTH_DAYS_FONT_COLOR_SET,
    Config::PREVIOUS_MONTH_DAYS_DATE_FORMAT,
    $holidays_previous_month
);


// --------------------------------------------------------------------------------
// Dither the image
// --------------------------------------------------------------------------------

if (!is_null($dither) && isset(Config::DITHER_PALETTE[$dither]["palette"])) {
    $converter = new \StudioDemmys\lib\GDIndexedColorConverter\GDIndexedColorConverter();
    $dithered = $converter->convertToIndexedColor($image, Config::DITHER_PALETTE[$dither]["palette"],
        Config::DITHER_PALETTE[$dither]["amount"] ?? 0.75, !is_null($pack_bits));
    if (!is_null($pack_bits)) {
        imagedestroy($image);
    }
    $image = $dithered;
}


// --------------------------------------------------------------------------------
// Export png or the binary
// --------------------------------------------------------------------------------

if (is_null($pack_bits)) {
    header('Content-Type: image/png');
    imagepng($image);
    imagedestroy($image);
    exit();
}

if ($pack_bits == 4) {
    $output = "";
    $buf = null;
    foreach ($image as $yx) {
        foreach ($yx as $x) {
            if (is_null($buf)) {
                $buf = $x;
            } else {
                $buf = pack("C", ($buf << 4) | $x);
                $output .= $buf;
                $buf = null;
            }
        }
    }
    if (!is_null($buf)) {
        $output .= $buf << 4;
    }
    header('Content-Type: application/octet-stream');
    echo $output;
} else {
    exit("error");
}




