<?php

namespace StudioDemmys\CalendarImage;

if (!defined('CALENDARIMAGE_UNIQUE_ID'))
    define("CALENDARIMAGE_UNIQUE_ID", bin2hex(random_bytes(4)));

require_once dirname(__FILE__)."/class/Common.php";
require_once dirname(__FILE__)."/class/Config.php";

Config::loadConfig();
Config::getConfigOrSetIfUndefined("logging/level", "debug");
Config::getConfigOrSetIfUndefined("logging/file", "./log/log.txt");

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


// --------------------------------------------------------------------------------
// Initialize
// --------------------------------------------------------------------------------

$timezone = Config::getConfigOrSetIfUndefined("timezone", date_default_timezone_get());

$now = new \DateTimeImmutable("now", new \DateTimeZone($timezone));
Logging::debug("now: " . $now->format(\DateTimeInterface::ATOM));

$dither = null;
$pack_bits = null;

// --------------------------------------------------------------------------------
// Define a rendering mode
// --------------------------------------------------------------------------------

if (isset($_GET["mode"])) {
    $mode = strtolower(Common::sanitizeUserInput($_GET["mode"]));
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

// --------------------------------------------------------------------------------
// Define a date
// --------------------------------------------------------------------------------

if (isset($_GET["date"])) {
    $query_date = strtolower(Common::sanitizeUserInput($_GET["date"]));
    // set $now when valid yyyymmdd is passed via GET
    if (preg_match('/^\d{8}$/', $query_date) === 1) {
        try {
            $target_date = new \DateTimeImmutable($query_date, new \DateTimeZone($timezone));
        } catch (\Exception) {
            $target_date = $now;
        }
        $now = $target_date;
    }
}

// --------------------------------------------------------------------------------
// Define a calendar style
// --------------------------------------------------------------------------------

$style = Config::getConfig("default_style");
if (isset($_GET["style"])) {
    $query_style = strtolower(Common::sanitizeUserInput($_GET["style"]));
    if (empty(Config::getConfigOrSetIfUndefined("style/{$query_style}")))
        $query_style = $style;
    $style = $query_style;
}


// --------------------------------------------------------------------------------
// Prepare a base image
// --------------------------------------------------------------------------------


$image = imagecreatefromstring(file_get_contents(Config::getConfig("style/{$style}/background_image")));
if ($image === false)
    exit("error");


// --------------------------------------------------------------------------------
// Render labels
// --------------------------------------------------------------------------------

$labels = Config::getConfigOrSetIfUndefined("style/{$style}/labels");
if (is_array($labels)) {
    foreach ($labels as $label_name => $label) {
        Logging::debug("Write a label -- {$label_name}");
        $target_date = $now->modify(Config::getConfigOrSetIfUndefined("style/{$style}/labels/{$label_name}/diff",
            "this day"));
        Logging::debug("target date: " . $target_date->format(\DateTimeInterface::ATOM));
        
        $holidays = new HolidaysOfTheYear($target_date, Config::getConfig("style/{$style}/holiday"), true);
        $print_text = Calendar::getPrintText(
            $target_date,
            Config::getConfig("style/{$style}/labels/{$label_name}/format"),
            $holidays,
            Config::getConfigOrSetIfUndefined("style/{$style}/labels/{$label_name}/locale"),
            $timezone
        );
        
        $default_color = Config::getConfigOrSetIfUndefined("style/{$style}/labels/{$label_name}/font/color", [0, 0, 0, 0]);
        
        $text_color = Calendar::getTextColorOfTheDay(
            $image,
            $target_date,
            Config::getConfigOrSetIfUndefined("style/{$style}/labels/{$label_name}/font/wday_colors", []),
            $default_color
        );
        if (Config::getConfigOrSetIfUndefined("style/{$style}/labels/{$label_name}/font/holiday_color", false)) {
            $holiday_color = Calendar::getTextColorOfHoliday($image, $target_date, $holidays);
            $text_color = $holiday_color ?? $text_color;
        }
        
        $text_box = Image::writeTextInBox(
            $image,
            Config::getConfig("style/{$style}/labels/{$label_name}/box/position"),
            Config::getConfig("style/{$style}/labels/{$label_name}/box/size"),
            Config::getConfig("style/{$style}/labels/{$label_name}/font/size"),
            Config::getConfig("style/{$style}/labels/{$label_name}/font/file"),
            $text_color,
            $print_text
        );
        
        if ($text_box === false)
            exit("error");
    }
}


// --------------------------------------------------------------------------------
// Render calendars
// --------------------------------------------------------------------------------

$calendars = Config::getConfigOrSetIfUndefined("style/{$style}/calendars");
if (is_array($calendars)) {
    foreach ($calendars as $calendar_name => $calendar) {
        Logging::debug("Write a calendar -- {$calendar_name}");
        $target_date = $now->modify(Config::getConfigOrSetIfUndefined("style/{$style}/calendars/{$calendar_name}/diff",
            "this day"));
        Logging::debug("target date: " . $target_date->format(\DateTimeInterface::ATOM));
        
        Calendar::renderDaysCalendar(
            $image,
            $target_date,
            Config::getConfigOrSetIfUndefined("style/{$style}/first_wday", 0),
            Config::getConfig("style/{$style}/calendars/{$calendar_name}/text_boxes"),
            Config::getConfig("style/{$style}/calendars/{$calendar_name}/font/size"),
            Config::getConfig("style/{$style}/calendars/{$calendar_name}/font/file"),
            Config::getConfigOrSetIfUndefined("style/{$style}/calendars/{$calendar_name}/font/wday_colors", []),
            Config::getConfigOrSetIfUndefined("style/{$style}/calendars/{$calendar_name}/font/color", [0, 0, 0, 0]),
            Config::getConfigOrSetIfUndefined("style/{$style}/calendars/{$calendar_name}/font/holiday_color"),
            Config::getConfig("style/{$style}/calendars/{$calendar_name}/format"),
            new HolidaysOfTheYear($target_date, Config::getConfig("style/{$style}/holiday"), true),
            Config::getConfigOrSetIfUndefined("style/{$style}/calendars/{$calendar_name}/locale"),
            $timezone,
            Config::getConfigOrSetIfUndefined("style/{$style}/calendars/{$calendar_name}/illumination_boxes")
        );
        
    }
}


// --------------------------------------------------------------------------------
// Render external images
// --------------------------------------------------------------------------------

$external_images = Config::getConfigOrSetIfUndefined("style/{$style}/images");
if (is_array($external_images)) {
    foreach ($external_images as $external_image_name => $external_image) {
        Logging::debug("Render an external image -- {$external_image_name}");
        
        $source_image_url = Config::getConfig("style/{$style}/images/{$external_image_name}/url");
        $source_image = imagecreatefromstring(file_get_contents($source_image_url));
        if ($source_image !== false) {
            $source_image_width = imagesx($source_image);
            $source_image_height = imagesy($source_image);
            imagecopyresampled(
                $image,
                $source_image,
                (Config::getConfig("style/{$style}/images/{$external_image_name}/box/position"))[0],
                (Config::getConfig("style/{$style}/images/{$external_image_name}/box/position"))[1],
                0,
                0,
                (Config::getConfig("style/{$style}/images/{$external_image_name}/box/size"))[0],
                (Config::getConfig("style/{$style}/images/{$external_image_name}/box/size"))[1],
                $source_image_width,
                $source_image_height
            );
        } else {
            Logging::warn("Failed to load the image -- {$source_image_url}");
            if (Config::getConfigOrSetIfUndefined("style/{$style}/images/{$external_image_name}/mandatory", false))
                exit("error");
        }
    }
}

// --------------------------------------------------------------------------------
// Dither the image
// --------------------------------------------------------------------------------

$palette = null;
if (!is_null($dither))
    $palette = Config::getConfigOrSetIfUndefined("dither_palette/{$dither}/palette");

if (!is_null($palette)) {
    $converter = new \StudioDemmys\lib\GDIndexedColorConverter\GDIndexedColorConverter();
    $dithered = $converter->convertToIndexedColor($image, $palette,
        Config::getConfigOrSetIfUndefined("dither_palette/{$dither}/amount") ?? 0.75, !is_null($pack_bits));
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
    ob_start();
    imagepng($image);
    $output = ob_get_contents();
    ob_end_clean();
    imagedestroy($image);
    header('Content-Length:' . strlen($output));
    echo $output;
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
    header('Content-Length:' . strlen($output));
    echo $output;
} else {
    exit("error");
}




