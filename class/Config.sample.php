<?php

namespace StudioDemmys\CalendarImage;

class Config
{
    // --------------------------------------------------------------------------------
    // Common configs
    // --------------------------------------------------------------------------------
    const LOG_FILE = "./log/log.txt";
    const LOG_LEVEL = "debug";
    
    const TIMEZONE = "Asia/Tokyo";
    const CALENDAR_LOCALE = "ja_JP@calendar=japanese";
    
    // BACKGROUND_IMAGE
    // calendar background
    const BACKGROUND_IMAGE = "./image/background.png";
    
    // Dither
    const DITHER_PALETTE = [
        "7color" => [
            "palette" => [
                0x0 => [  0,   0,   0], // black
                0x1 => [255, 255, 255], // white
                0x2 => [  0, 255,   0], // green
                0x3 => [  0,   0, 255], // blue
                0x4 => [255,   0,   0], // red
                0x5 => [255, 255,   0], // yellow
                0x6 => [255, 170,   0], // orange
            ],
            "amount" => 0.75,
        ],
    ];
    
    // FIRST_WDAY
    // first day of week
    // 0=Sunday, .., 6=Saturday
    const FIRST_WDAY = 0;
    
    // HOLIDAYS_RULE_SET
    // define holiday rules
    // rule: A date format to represent the holiday. https://www.php.net/manual/en/datetime.formats.php
    //   (Special keyword)
    //    - The word "VED" is replaced to the date of Vernal Equinox Day, usually "3/20" or "3/21"
    //    - The word "AED" is replaced to the date of Autumnal Equinox Day, usually "9/22" or "9/23"
    // name = null | string : Holiday name
    // type = {national_holiday | work_holiday}
    // substitute_when = null | [{sunday | saturday | national_holiday | work_holiday}..] : When to substitute the holiday
    // substitution_avoid = null | [{sunday | saturday | national_holiday | work_holiday}..] : Days to exclude from candidates of the substitute holiday
    // substitute holiday defined in Article 3, paragraph (2) of National Holiday Act in Japan can be expressed by
    //   substitute_when = ["sunday"] and substitution_avoid = ["national_holiday"]
    // 祝日法第3条第2項 「国民の祝日」が日曜日に当たるときは、その日後においてその日に最も近い「国民の祝日」でない日を休日とする。
    const HOLIDAYS_RULE_SET = [
        ["rule" => "1/1", "name" => "元日", "type" => "national_holiday", "color" => [255, 0, 0, 0], "substitute_when" => ["sunday"], "substitution_avoid" => ["national_holiday"]],
        ["rule" => "second monday of January", "name" => "成人の日", "type" => "national_holiday", "color" => [255, 0, 0, 0], "substitute_when" => ["sunday"], "substitution_avoid" => ["national_holiday"]],
        ["rule" => "2/11", "name" => "建国記念の日", "type" => "national_holiday", "color" => [255, 0, 0, 0], "substitute_when" => ["sunday"], "substitution_avoid" => ["national_holiday"]],
        ["rule" => "2/23", "name" => "天皇誕生日", "type" => "national_holiday", "color" => [255, 0, 0, 0], "substitute_when" => ["sunday"], "substitution_avoid" => ["national_holiday"]],
        ["rule" => "VED", "name" => "春分の日", "type" => "national_holiday", "color" => [255, 0, 0, 0], "substitute_when" => ["sunday"], "substitution_avoid" => ["national_holiday"]],
        ["rule" => "4/29", "name" => "昭和の日", "type" => "national_holiday", "color" => [255, 0, 0, 0], "substitute_when" => ["sunday"], "substitution_avoid" => ["national_holiday"]],
        ["rule" => "5/3", "name" => "憲法記念日", "type" => "national_holiday", "color" => [255, 0, 0, 0], "substitute_when" => ["sunday"], "substitution_avoid" => ["national_holiday"]],
        ["rule" => "5/4", "name" => "みどりの日", "type" => "national_holiday", "color" => [255, 0, 0, 0], "substitute_when" => ["sunday"], "substitution_avoid" => ["national_holiday"]],
        ["rule" => "5/5", "name" => "こどもの日", "type" => "national_holiday", "color" => [255, 0, 0, 0], "substitute_when" => ["sunday"], "substitution_avoid" => ["national_holiday"]],
        ["rule" => "third monday of July", "name" => "海の日", "type" => "national_holiday", "color" => [255, 0, 0, 0], "substitute_when" => ["sunday"], "substitution_avoid" => ["national_holiday"]],
        ["rule" => "8/11", "name" => "山の日", "type" => "national_holiday", "color" => [255, 0, 0, 0], "substitute_when" => ["sunday"], "substitution_avoid" => ["national_holiday"]],
        ["rule" => "third monday of September", "name" => "敬老の日", "type" => "national_holiday", "color" => [255, 0, 0, 0], "substitute_when" => ["sunday"], "substitution_avoid" => ["national_holiday"]],
        ["rule" => "AED", "name" => "秋分の日", "type" => "national_holiday", "color" => [255, 0, 0, 0], "substitute_when" => ["sunday"], "substitution_avoid" => ["national_holiday"]],
        ["rule" => "second monday of October", "name" => "スポーツの日", "type" => "national_holiday", "color" => [255, 0, 0, 0], "substitute_when" => ["sunday"], "substitution_avoid" => ["national_holiday"]],
        ["rule" => "11/3", "name" => "文化の日", "type" => "national_holiday", "color" => [255, 0, 0, 0], "substitute_when" => ["sunday"], "substitution_avoid" => ["national_holiday"]],
        ["rule" => "11/23", "name" => "勤労感謝の日", "type" => "national_holiday", "color" => [255, 0, 0, 0], "substitute_when" => ["sunday"], "substitution_avoid" => ["national_holiday"]],
        ["rule" => "12/29", "name" => "年末年始休", "type" => "work_holiday", "color" => [255, 0, 0, 0], "substitute_when" => [], "substitution_avoid" => []],
        ["rule" => "12/30", "name" => "年末年始休", "type" => "work_holiday", "color" => [255, 0, 0, 0], "substitute_when" => [], "substitution_avoid" => []],
        ["rule" => "12/31", "name" => "年末年始休", "type" => "work_holiday", "color" => [255, 0, 0, 0], "substitute_when" => [], "substitution_avoid" => []],
        ["rule" => "1/2", "name" => "年末年始休", "type" => "work_holiday", "color" => [255, 0, 0, 0], "substitute_when" => [], "substitution_avoid" => []],
        ["rule" => "1/3", "name" => "年末年始休", "type" => "work_holiday", "color" => [255, 0, 0, 0], "substitute_when" => [], "substitution_avoid" => []],
    ];
    const DEFAULT_HOLIDAY_NAME = "休日";
    const SUBSTITUTE_HOLIDAY_NAME = "振替休日";
    
    // HOLIDAY_NAME_SEPARATOR
    // a separator string when multiple holidays are on the same day.
    // set `null` to show only one holiday name
    const HOLIDAY_NAME_SEPARATOR = "/";
    const DEFAULT_HOLIDAY_COLOR = [0, 255, 0, 0];
    
    // HOLIDAY_RULE_SANDWICH
    //   true: Activate Article 3, paragraph (3) of National Holiday Act in Japan.
    //   false: Deactivate Article 3, paragraph (3) of National Holiday Act in Japan.
    //   祝日法第3条第3項 その前日及び翌日が「国民の祝日」である日（「国民の祝日」でない日に限る。）は、休日とする。
    const HOLIDAY_RULE_SANDWICH = true;
    
    
    // --------------------------------------------------------------------------------
    // Year in western format representation configs
    // ex. 2024
    // --------------------------------------------------------------------------------
    const THIS_YEAR_IN_WESTERN_BOX = [
        "position" => [230, 3], "size" => [60, 25]
    ];
    const THIS_YEAR_IN_WESTERN_DATE_FORMAT = "Y";
    const THIS_YEAR_IN_WESTERN_FONT_FILE = "./font/ZenMaruGothic-Black.ttf";
    const THIS_YEAR_IN_WESTERN_FONT_SIZE = 16;
    const THIS_YEAR_IN_WESTERN_FONT_COLOR = [0, 0, 0, 0];
    
    
    // --------------------------------------------------------------------------------
    // Year in local format representation configs
    // ex. 令和6年
    // --------------------------------------------------------------------------------
    const THIS_YEAR_IN_LOCAL_BOX = [
        "position" => [290, 3], "size" => [80, 25]
    ];
    const THIS_YEAR_IN_LOCAL_DATE_FORMAT = "Gy";
    const THIS_YEAR_IN_LOCAL_FONT_FILE = "./font/ZenMaruGothic-Black.ttf";
    const THIS_YEAR_IN_LOCAL_FONT_SIZE = 16;
    const THIS_YEAR_IN_LOCAL_FONT_COLOR = [0, 0, 0, 0];
    
    
    // --------------------------------------------------------------------------------
    // Days of this month representation configs
    // --------------------------------------------------------------------------------
    
    // THIS_MONTH_DAYS_BOX_SET
    // the position and the size of the boxes each day is written in.
    // every week has seven days, six weeks are necessary to render all days of a month,
    // then we should define (7x6=)42 boxes.
    const THIS_MONTH_DAYS_BOX_SET = [
        ["position" => [ 20, 180], "size" => [80, 35]],
        ["position" => [100, 180], "size" => [80, 35]],
        ["position" => [180, 180], "size" => [80, 35]],
        ["position" => [260, 180], "size" => [80, 35]],
        ["position" => [340, 180], "size" => [80, 35]],
        ["position" => [420, 180], "size" => [80, 35]],
        ["position" => [500, 180], "size" => [80, 35]],
        
        ["position" => [ 20, 230], "size" => [80, 35]],
        ["position" => [100, 230], "size" => [80, 35]],
        ["position" => [180, 230], "size" => [80, 35]],
        ["position" => [260, 230], "size" => [80, 35]],
        ["position" => [340, 230], "size" => [80, 35]],
        ["position" => [420, 230], "size" => [80, 35]],
        ["position" => [500, 230], "size" => [80, 35]],
        
        ["position" => [ 20, 280], "size" => [80, 35]],
        ["position" => [100, 280], "size" => [80, 35]],
        ["position" => [180, 280], "size" => [80, 35]],
        ["position" => [260, 280], "size" => [80, 35]],
        ["position" => [340, 280], "size" => [80, 35]],
        ["position" => [420, 280], "size" => [80, 35]],
        ["position" => [500, 280], "size" => [80, 35]],
        
        ["position" => [ 20, 330], "size" => [80, 35]],
        ["position" => [100, 330], "size" => [80, 35]],
        ["position" => [180, 330], "size" => [80, 35]],
        ["position" => [260, 330], "size" => [80, 35]],
        ["position" => [340, 330], "size" => [80, 35]],
        ["position" => [420, 330], "size" => [80, 35]],
        ["position" => [500, 330], "size" => [80, 35]],
        
        ["position" => [ 20, 380], "size" => [80, 35]],
        ["position" => [100, 380], "size" => [80, 35]],
        ["position" => [180, 380], "size" => [80, 35]],
        ["position" => [260, 380], "size" => [80, 35]],
        ["position" => [340, 380], "size" => [80, 35]],
        ["position" => [420, 380], "size" => [80, 35]],
        ["position" => [500, 380], "size" => [80, 35]],
        
        ["position" => [ 20, 430], "size" => [80, 35]],
        ["position" => [100, 430], "size" => [80, 35]],
        ["position" => [180, 430], "size" => [80, 35]],
        ["position" => [260, 430], "size" => [80, 35]],
        ["position" => [340, 430], "size" => [80, 35]],
        ["position" => [420, 430], "size" => [80, 35]],
        ["position" => [500, 430], "size" => [80, 35]],
    ];
    
    const THIS_MONTH_DAYS_DATE_FORMAT = "j";
    const THIS_MONTH_DAYS_FONT_FILE = "./font/ZenMaruGothic-Black.ttf";
    const THIS_MONTH_DAYS_FONT_SIZE = 32;
    const THIS_MONTH_DAYS_FONT_COLOR_SET = [
        "weekday" => [0, 0, 0, 0],
        "saturday" => [0, 0, 255, 0],
        "sunday" => [255, 0, 0, 0],
    ];
    
    
    // THIS_MONTH_HOLIDAYS_BOX_SET
    // the position and the size of the boxes for the holiday name of each day.
    const THIS_MONTH_HOLIDAYS_BOX_SET = [
        ["position" => [ 20, 215], "size" => [80, 15]],
        ["position" => [100, 215], "size" => [80, 15]],
        ["position" => [180, 215], "size" => [80, 15]],
        ["position" => [260, 215], "size" => [80, 15]],
        ["position" => [340, 215], "size" => [80, 15]],
        ["position" => [420, 215], "size" => [80, 15]],
        ["position" => [500, 215], "size" => [80, 15]],
        
        ["position" => [ 20, 265], "size" => [80, 15]],
        ["position" => [100, 265], "size" => [80, 15]],
        ["position" => [180, 265], "size" => [80, 15]],
        ["position" => [260, 265], "size" => [80, 15]],
        ["position" => [340, 265], "size" => [80, 15]],
        ["position" => [420, 265], "size" => [80, 15]],
        ["position" => [500, 265], "size" => [80, 15]],
        
        ["position" => [ 20, 315], "size" => [80, 15]],
        ["position" => [100, 315], "size" => [80, 15]],
        ["position" => [180, 315], "size" => [80, 15]],
        ["position" => [260, 315], "size" => [80, 15]],
        ["position" => [340, 315], "size" => [80, 15]],
        ["position" => [420, 315], "size" => [80, 15]],
        ["position" => [500, 315], "size" => [80, 15]],
        
        ["position" => [ 20, 365], "size" => [80, 15]],
        ["position" => [100, 365], "size" => [80, 15]],
        ["position" => [180, 365], "size" => [80, 15]],
        ["position" => [260, 365], "size" => [80, 15]],
        ["position" => [340, 365], "size" => [80, 15]],
        ["position" => [420, 365], "size" => [80, 15]],
        ["position" => [500, 365], "size" => [80, 15]],
        
        ["position" => [ 20, 415], "size" => [80, 15]],
        ["position" => [100, 415], "size" => [80, 15]],
        ["position" => [180, 415], "size" => [80, 15]],
        ["position" => [260, 415], "size" => [80, 15]],
        ["position" => [340, 415], "size" => [80, 15]],
        ["position" => [420, 415], "size" => [80, 15]],
        ["position" => [500, 415], "size" => [80, 15]],
        
        ["position" => [ 20, 465], "size" => [80, 15]],
        ["position" => [100, 465], "size" => [80, 15]],
        ["position" => [180, 465], "size" => [80, 15]],
        ["position" => [260, 465], "size" => [80, 15]],
        ["position" => [340, 465], "size" => [80, 15]],
        ["position" => [420, 465], "size" => [80, 15]],
        ["position" => [500, 465], "size" => [80, 15]],
    ];
    const THIS_MONTH_HOLIDAYS_FONT_FILE = "./font/ZenMaruGothic-Medium.ttf";
    const THIS_MONTH_HOLIDAYS_FONT_SIZE = 9;
    
    
    // THIS_MONTH_DAYS_BACKGROUND_COLOR and THIS_MONTH_DAYS_LINE_COLOR
    // the background color and the line color to illuminate today
    const THIS_MONTH_DAYS_BACKGROUND_COLOR = [255, 255, 127, 0];
    const THIS_MONTH_DAYS_LINE_COLOR = [0, 0, 0, 0];
    
    // THIS_MONTH_TARGET_DAY_BOX_SET
    // the area to illuminate today
    // shape = {rectangle | circle}
    const THIS_MONTH_TARGET_DAY_BOX_SET = [
        ["position" => [ 20, 180], "size" => [80, 50], "shape" => "rectangle"],
        ["position" => [100, 180], "size" => [80, 50], "shape" => "rectangle"],
        ["position" => [180, 180], "size" => [80, 50], "shape" => "rectangle"],
        ["position" => [260, 180], "size" => [80, 50], "shape" => "rectangle"],
        ["position" => [340, 180], "size" => [80, 50], "shape" => "rectangle"],
        ["position" => [420, 180], "size" => [80, 50], "shape" => "rectangle"],
        ["position" => [500, 180], "size" => [80, 50], "shape" => "rectangle"],
        
        ["position" => [ 20, 230], "size" => [80, 50], "shape" => "rectangle"],
        ["position" => [100, 230], "size" => [80, 50], "shape" => "rectangle"],
        ["position" => [180, 230], "size" => [80, 50], "shape" => "rectangle"],
        ["position" => [260, 230], "size" => [80, 50], "shape" => "rectangle"],
        ["position" => [340, 230], "size" => [80, 50], "shape" => "rectangle"],
        ["position" => [420, 230], "size" => [80, 50], "shape" => "rectangle"],
        ["position" => [500, 230], "size" => [80, 50], "shape" => "rectangle"],
        
        ["position" => [ 20, 280], "size" => [80, 50], "shape" => "rectangle"],
        ["position" => [100, 280], "size" => [80, 50], "shape" => "rectangle"],
        ["position" => [180, 280], "size" => [80, 50], "shape" => "rectangle"],
        ["position" => [260, 280], "size" => [80, 50], "shape" => "rectangle"],
        ["position" => [340, 280], "size" => [80, 50], "shape" => "rectangle"],
        ["position" => [420, 280], "size" => [80, 50], "shape" => "rectangle"],
        ["position" => [500, 280], "size" => [80, 50], "shape" => "rectangle"],
        
        ["position" => [ 20, 330], "size" => [80, 50], "shape" => "rectangle"],
        ["position" => [100, 330], "size" => [80, 50], "shape" => "rectangle"],
        ["position" => [180, 330], "size" => [80, 50], "shape" => "rectangle"],
        ["position" => [260, 330], "size" => [80, 50], "shape" => "rectangle"],
        ["position" => [340, 330], "size" => [80, 50], "shape" => "rectangle"],
        ["position" => [420, 330], "size" => [80, 50], "shape" => "rectangle"],
        ["position" => [500, 330], "size" => [80, 50], "shape" => "rectangle"],
        
        ["position" => [ 20, 380], "size" => [80, 50], "shape" => "rectangle"],
        ["position" => [100, 380], "size" => [80, 50], "shape" => "rectangle"],
        ["position" => [180, 380], "size" => [80, 50], "shape" => "rectangle"],
        ["position" => [260, 380], "size" => [80, 50], "shape" => "rectangle"],
        ["position" => [340, 380], "size" => [80, 50], "shape" => "rectangle"],
        ["position" => [420, 380], "size" => [80, 50], "shape" => "rectangle"],
        ["position" => [500, 380], "size" => [80, 50], "shape" => "rectangle"],
        
        ["position" => [ 20, 430], "size" => [80, 50], "shape" => "rectangle"],
        ["position" => [100, 430], "size" => [80, 50], "shape" => "rectangle"],
        ["position" => [180, 430], "size" => [80, 50], "shape" => "rectangle"],
        ["position" => [260, 430], "size" => [80, 50], "shape" => "rectangle"],
        ["position" => [340, 430], "size" => [80, 50], "shape" => "rectangle"],
        ["position" => [420, 430], "size" => [80, 50], "shape" => "rectangle"],
        ["position" => [500, 430], "size" => [80, 50], "shape" => "rectangle"],
    ];
    
    
    // --------------------------------------------------------------------------------
    // Number of this month representation configs
    // --------------------------------------------------------------------------------
    const THIS_MONTH_IN_NUMERICAL_BOX = [
        "position" => [230, 30], "size" => [140, 100]
    ];
    const THIS_MONTH_IN_NUMERICAL_DATE_FORMAT = "n";
    const THIS_MONTH_IN_NUMERICAL_FONT_FILE = "./font/ZenMaruGothic-Black.ttf";
    const THIS_MONTH_IN_NUMERICAL_FONT_SIZE = 80;
    const THIS_MONTH_IN_NUMERICAL_FONT_COLOR = [0, 0, 0, 0];
    
    
    // --------------------------------------------------------------------------------
    // Name of this month representation configs
    // --------------------------------------------------------------------------------
    const THIS_MONTH_IN_TEXTUAL_BOX = [
        "position" => [230, 130], "size" => [140, 25]
    ];
    const THIS_MONTH_IN_TEXTUAL_DATE_FORMAT = "F";
    const THIS_MONTH_IN_TEXTUAL_FONT_FILE = "./font/ZenMaruGothic-Black.ttf";
    const THIS_MONTH_IN_TEXTUAL_FONT_SIZE = 16;
    const THIS_MONTH_IN_TEXTUAL_FONT_COLOR = [0, 0, 0, 0];
    
    
    // --------------------------------------------------------------------------------
    // Next month representation configs
    // --------------------------------------------------------------------------------
    const NEXT_MONTH_DAYS_BOX_SET = [
        ["position" => [398,  35], "size" => [26, 20]],
        ["position" => [424,  35], "size" => [26, 20]],
        ["position" => [450,  35], "size" => [26, 20]],
        ["position" => [476,  35], "size" => [26, 20]],
        ["position" => [502,  35], "size" => [26, 20]],
        ["position" => [528,  35], "size" => [26, 20]],
        ["position" => [554,  35], "size" => [26, 20]],
        
        ["position" => [398,  55], "size" => [26, 20]],
        ["position" => [424,  55], "size" => [26, 20]],
        ["position" => [450,  55], "size" => [26, 20]],
        ["position" => [476,  55], "size" => [26, 20]],
        ["position" => [502,  55], "size" => [26, 20]],
        ["position" => [528,  55], "size" => [26, 20]],
        ["position" => [554,  55], "size" => [26, 20]],
        
        ["position" => [398,  75], "size" => [26, 20]],
        ["position" => [424,  75], "size" => [26, 20]],
        ["position" => [450,  75], "size" => [26, 20]],
        ["position" => [476,  75], "size" => [26, 20]],
        ["position" => [502,  75], "size" => [26, 20]],
        ["position" => [528,  75], "size" => [26, 20]],
        ["position" => [554,  75], "size" => [26, 20]],
        
        ["position" => [398,  95], "size" => [26, 20]],
        ["position" => [424,  95], "size" => [26, 20]],
        ["position" => [450,  95], "size" => [26, 20]],
        ["position" => [476,  95], "size" => [26, 20]],
        ["position" => [502,  95], "size" => [26, 20]],
        ["position" => [528,  95], "size" => [26, 20]],
        ["position" => [554,  95], "size" => [26, 20]],
        
        ["position" => [398, 115], "size" => [26, 20]],
        ["position" => [424, 115], "size" => [26, 20]],
        ["position" => [450, 115], "size" => [26, 20]],
        ["position" => [476, 115], "size" => [26, 20]],
        ["position" => [502, 115], "size" => [26, 20]],
        ["position" => [528, 115], "size" => [26, 20]],
        ["position" => [554, 115], "size" => [26, 20]],
        
        ["position" => [398, 135], "size" => [26, 20]],
        ["position" => [424, 135], "size" => [26, 20]],
        ["position" => [450, 135], "size" => [26, 20]],
        ["position" => [476, 135], "size" => [26, 20]],
        ["position" => [502, 135], "size" => [26, 20]],
        ["position" => [528, 135], "size" => [26, 20]],
        ["position" => [554, 135], "size" => [26, 20]],
    ];
    
    const NEXT_MONTH_DAYS_DATE_FORMAT = "j";
    const NEXT_MONTH_DAYS_FONT_FILE = "./font/ZenMaruGothic-Medium.ttf";
    const NEXT_MONTH_DAYS_FONT_SIZE = 14;
    const NEXT_MONTH_DAYS_FONT_COLOR_SET = [
        "weekday" => [0, 0, 0, 0],
        "saturday" => [0, 0, 255, 0],
        "sunday" => [255, 0, 0, 0],
    ];
    
    
    const NEXT_MONTH_IN_NUMERICAL_BOX = [
        "position" => [418, 5], "size" => [40, 30]
    ];
    const NEXT_MONTH_IN_NUMERICAL_DATE_FORMAT = "n";
    const NEXT_MONTH_IN_NUMERICAL_FONT_FILE = "./font/ZenMaruGothic-Medium.ttf";
    const NEXT_MONTH_IN_NUMERICAL_FONT_SIZE = 20;
    const NEXT_MONTH_IN_NUMERICAL_FONT_COLOR = [0, 0, 0, 0];
    
    const NEXT_MONTH_IN_TEXTUAL_BOX = [
        "position" => [463, 10], "size" => [100, 20]
    ];
    const NEXT_MONTH_IN_TEXTUAL_DATE_FORMAT = "F";
    const NEXT_MONTH_IN_TEXTUAL_FONT_FILE = "./font/ZenMaruGothic-Medium.ttf";
    const NEXT_MONTH_IN_TEXTUAL_FONT_SIZE = 14;
    const NEXT_MONTH_IN_TEXTUAL_FONT_COLOR = [0, 0, 0, 0];
    
    
    // --------------------------------------------------------------------------------
    // Previous month representation configs
    // --------------------------------------------------------------------------------
    const PREVIOUS_MONTH_DAYS_BOX_SET = [
        ["position" => [ 20,  35], "size" => [26, 20]],
        ["position" => [ 46,  35], "size" => [26, 20]],
        ["position" => [ 72,  35], "size" => [26, 20]],
        ["position" => [ 98,  35], "size" => [26, 20]],
        ["position" => [124,  35], "size" => [26, 20]],
        ["position" => [150,  35], "size" => [26, 20]],
        ["position" => [176,  35], "size" => [26, 20]],
        
        ["position" => [ 20,  55], "size" => [26, 20]],
        ["position" => [ 46,  55], "size" => [26, 20]],
        ["position" => [ 72,  55], "size" => [26, 20]],
        ["position" => [ 98,  55], "size" => [26, 20]],
        ["position" => [124,  55], "size" => [26, 20]],
        ["position" => [150,  55], "size" => [26, 20]],
        ["position" => [176,  55], "size" => [26, 20]],
        
        ["position" => [ 20,  75], "size" => [26, 20]],
        ["position" => [ 46,  75], "size" => [26, 20]],
        ["position" => [ 72,  75], "size" => [26, 20]],
        ["position" => [ 98,  75], "size" => [26, 20]],
        ["position" => [124,  75], "size" => [26, 20]],
        ["position" => [150,  75], "size" => [26, 20]],
        ["position" => [176,  75], "size" => [26, 20]],
        
        ["position" => [ 20,  95], "size" => [26, 20]],
        ["position" => [ 46,  95], "size" => [26, 20]],
        ["position" => [ 72,  95], "size" => [26, 20]],
        ["position" => [ 98,  95], "size" => [26, 20]],
        ["position" => [124,  95], "size" => [26, 20]],
        ["position" => [150,  95], "size" => [26, 20]],
        ["position" => [176,  95], "size" => [26, 20]],
        
        ["position" => [ 20, 115], "size" => [26, 20]],
        ["position" => [ 46, 115], "size" => [26, 20]],
        ["position" => [ 72, 115], "size" => [26, 20]],
        ["position" => [ 98, 115], "size" => [26, 20]],
        ["position" => [124, 115], "size" => [26, 20]],
        ["position" => [150, 115], "size" => [26, 20]],
        ["position" => [176, 115], "size" => [26, 20]],
        
        ["position" => [ 20, 135], "size" => [26, 20]],
        ["position" => [ 46, 135], "size" => [26, 20]],
        ["position" => [ 72, 135], "size" => [26, 20]],
        ["position" => [ 98, 135], "size" => [26, 20]],
        ["position" => [124, 135], "size" => [26, 20]],
        ["position" => [150, 135], "size" => [26, 20]],
        ["position" => [176, 135], "size" => [26, 20]],
    ];
    
    const PREVIOUS_MONTH_DAYS_DATE_FORMAT = "j";
    const PREVIOUS_MONTH_DAYS_FONT_FILE = "./font/ZenMaruGothic-Medium.ttf";
    const PREVIOUS_MONTH_DAYS_FONT_SIZE = 14;
    const PREVIOUS_MONTH_DAYS_FONT_COLOR_SET = [
        "weekday" => [0, 0, 0, 0],
        "saturday" => [0, 0, 255, 0],
        "sunday" => [255, 0, 0, 0],
    ];
    
    const PREVIOUS_MONTH_IN_NUMERICAL_BOX = [
        "position" => [40, 5], "size" => [40, 30]
    ];
    const PREVIOUS_MONTH_IN_NUMERICAL_DATE_FORMAT = "n";
    const PREVIOUS_MONTH_IN_NUMERICAL_FONT_FILE = "./font/ZenMaruGothic-Medium.ttf";
    const PREVIOUS_MONTH_IN_NUMERICAL_FONT_SIZE = 20;
    const PREVIOUS_MONTH_IN_NUMERICAL_FONT_COLOR = [0, 0, 0, 0];
    
    const PREVIOUS_MONTH_IN_TEXTUAL_BOX = [
        "position" => [85, 10], "size" => [100, 20]
    ];
    const PREVIOUS_MONTH_IN_TEXTUAL_DATE_FORMAT = "F";
    const PREVIOUS_MONTH_IN_TEXTUAL_FONT_FILE = "./font/ZenMaruGothic-Medium.ttf";
    const PREVIOUS_MONTH_IN_TEXTUAL_FONT_SIZE = 14;
    const PREVIOUS_MONTH_IN_TEXTUAL_FONT_COLOR = [0, 0, 0, 0];
    
}