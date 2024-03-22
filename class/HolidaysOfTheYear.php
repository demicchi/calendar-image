<?php

namespace StudioDemmys\CalendarImage;

class HolidaysOfTheYear
{
    /**
     * @var Holiday[] $holidays
     */
    protected array $holidays;
    protected \DateTimeImmutable $this_year;
    /**
     * @var HolidaySubstitution[] $substitution_overflown
     */
    protected array $substitution_overflown;
    
    public function __construct(\DateTimeInterface $date, bool $recursive = false)
    {
        $this->this_year = \DateTimeImmutable::createFromInterface($date);
        Logging::debug("this year: " . $this->this_year->format(\DateTimeInterface::ATOM));
        $this->initializeHolidayTable();
        $substitution_needed_list = $this->setHolidayToHolidayTable();
        if ($recursive) {
            $previous_year = new self($this->this_year->modify("previous year"), false);
            $substitution_overflown = $previous_year->getSubstituteHolidayOverflow();
            $substitution_needed_list = [...$substitution_needed_list, ...$substitution_overflown];
        }
        $this->setSubstituteHolidayToHolidayTable($substitution_needed_list);
        if (Config::HOLIDAY_RULE_SANDWICH)
            $this->setSandwichHolidayToHolidayTable();
        Logging::debug("the calculated holidays are -- " . print_r($this->holidays, true));
    }
    
    protected function initializeHolidayTable(): void
    {
        $this->holidays = [];
        $this->substitution_overflown = [];
        
        $first_day = \DateTimeImmutable::createFromInterface($this->this_year)->modify("1/1");
        Logging::debug("first day: " . $first_day->format(\DateTimeInterface::ATOM));
        $last_day = \DateTimeImmutable::createFromInterface($this->this_year)->modify("12/31");
        Logging::debug("last day: " . $last_day->format(\DateTimeInterface::ATOM));
        
        $target_day = \DateTime::createFromImmutable($first_day);
        while ($target_day <= $last_day) {
            Logging::debug("target day: " . $target_day->format(\DateTimeInterface::ATOM));
            $index = intval($target_day->format("z"));
            if ($target_day->format("w") == 0) {
                Logging::debug("this day is sunday");
                $this->holidays[$index] = new Holiday(\DateTimeImmutable::createFromMutable($target_day), DayType::Sunday);
            } elseif ($target_day->format("w") == 6) {
                Logging::debug("this day is saturday");
                $this->holidays[$index] = new Holiday(\DateTimeImmutable::createFromMutable($target_day), DayType::Saturday);
            }
            $target_day->modify("+1 day");
        }
    }
    
    /**
     * @return HolidaySubstitution[]
     */
    protected function setHolidayToHolidayTable(): array
    {
        $ved = $this->calculateVernalEquinoxDay()->format("n/j");
        $aed = $this->calculateAutumnalEquinoxDay()->format("n/j");
        $substitution_needed_list = [];
        foreach (Config::HOLIDAYS_RULE_SET as $target_rule) {
            $target_day = $this->this_year->modify(str_replace(["VED", "AED"], [$ved, $aed], $target_rule["rule"]));
            Logging::debug("target day: " . $target_day->format(\DateTimeInterface::ATOM));
            
            if ($target_day->format("Y") != $this->this_year->format("Y"))
                continue;
            
            Logging::debug("this day is a holiday -- " . $target_rule["name"]);
            
            Logging::debug("check if the target day needs a substitute holiday");
            if ($this->checkSubstitutionNeeded($target_day, $target_rule["substitute_when"])) {
                Logging::debug("substitution needed");
                $substitution_needed_list[] = new HolidaySubstitution($target_day, $target_rule["substitution_avoid"],
                    new Color($target_rule["color"]));
            }
            
            $index = intval($target_day->format("z"));
            $this->holidays[$index] = new Holiday(
                $target_day,
                DayType::tryFrom($target_rule["type"]) ?? DayType::NationalHoliday,
                $target_rule["name"],
                new Color($target_rule["color"])
            );
        }
        return $substitution_needed_list;
    }
    
    protected function setSandwichHolidayToHolidayTable(): void
    {
        $first_day = \DateTimeImmutable::createFromInterface($this->this_year)->modify("1/1");
        Logging::debug("first day: " . $first_day->format(\DateTimeInterface::ATOM));
        $last_day = \DateTimeImmutable::createFromInterface($this->this_year)->modify("12/31");
        Logging::debug("last day: " . $last_day->format(\DateTimeInterface::ATOM));
        
        $target_day = \DateTime::createFromImmutable($first_day);
        while ($target_day <= $last_day) {
            Logging::debug("target day: " . $target_day->format(\DateTimeInterface::ATOM));
            
            $holiday = $this->getHoliday($target_day);
            if (!is_null($holiday) && $holiday->type == DayType::NationalHoliday) {
                $target_day->modify("+1 day");
                continue;
            }
            
            $check_day = \DateTime::createFromInterface($target_day);
            $check_day->modify("-1 day");
            $holiday = $this->getHoliday($check_day);
            if (is_null($holiday) || $holiday->type != DayType::NationalHoliday) {
                $target_day->modify("+1 day");
                continue;
            }
            
            $check_day->modify("+2 day");
            $holiday = $this->getHoliday($check_day);
            if (is_null($holiday) || $holiday->type != DayType::NationalHoliday) {
                $target_day->modify("+1 day");
                continue;
            }
            
            Logging::debug("sandwich holiday detected!");
            $index = intval($target_day->format("z"));
            $this->holidays[$index] = new Holiday(\DateTimeImmutable::createFromMutable($target_day),
                DayType::SandwichHoliday, Config::DEFAULT_HOLIDAY_NAME,
                new Color(Config::DEFAULT_HOLIDAY_COLOR));
            $target_day->modify("+1 day");
        }
    }
    
    protected function checkSubstitutionNeeded(\DateTimeInterface $target_day, ?array $substitution_rule): bool
    {
        Logging::debug("target day: " . $target_day->format(\DateTimeInterface::ATOM));
        if (is_null($substitution_rule)) {
            Logging::debug("no rule");
            return false;
        }
        
        $index = intval($target_day->format("z"));
        if (!isset($this->holidays[$index])) {
            Logging::debug("no holiday is set");
            return false;
        }
        
        foreach ($substitution_rule as $rule) {
            if (strtolower($rule) == "sunday" && $this->holidays[$index]->type == DayType::Sunday) {
                Logging::debug("the target day is sunday and needs a substitution");
                return true;
            }
            if (strtolower($rule) == "saturday" && $this->holidays[$index]->type == DayType::Saturday) {
                Logging::debug("the target day is saturday and needs a substitution");
                return true;
            }
            if (strtolower($rule) == "national_holiday" && $this->holidays[$index]->type == DayType::NationalHoliday) {
                Logging::debug("the target day is a national holiday and needs a substitution");
                return true;
            }
            if (strtolower($rule) == "work_holiday" && $this->holidays[$index]->type == DayType::WorkHoliday) {
                Logging::debug("the target day is a work holiday and needs a substitution");
                return true;
            }
        }
        Logging::debug("the target day does not need a substitution");
        return false;
    }
    
    /**
     * @param HolidaySubstitution[] $substitution_list
     */
    protected function setSubstituteHolidayToHolidayTable(array $substitution_list): void
    {
        foreach ($substitution_list as $target) {
            $target_day = \DateTime::createFromImmutable($target->date);
            Logging::debug("target day: " . $target_day->format(\DateTimeInterface::ATOM));
            do {
                $target_day->modify("+1 day");
                if ($target_day->format("Y") != $this->this_year->format("Y")) {
                    Logging::debug("substitute day is overflown");
                    $this->substitution_overflown[] = $target;
                    break;
                }
            } while ($this->checkSubstitutionNeeded($target_day, $target->substitution_avoid));
            Logging::debug("substitute day: " . $target_day->format(\DateTimeInterface::ATOM));
            
            $index = intval($target_day->format("z"));
            if (isset($this->holidays[$index]) && !is_null($this->holidays[$index]->name) && !is_null(Config::HOLIDAY_NAME_SEPARATOR)) {
                $holiday_name = $this->holidays[$index]->name . Config::HOLIDAY_NAME_SEPARATOR . Config::SUBSTITUTE_HOLIDAY_NAME;
            } else {
                $holiday_name = Config::SUBSTITUTE_HOLIDAY_NAME;
            }
            $this->holidays[$index] = new Holiday(\DateTimeImmutable::createFromMutable($target_day),
                DayType::SubstituteHoliday, $holiday_name, $target->color);
        }
    }
    
    public function getHoliday(\DateTimeInterface $target_day): ?Holiday
    {
        Logging::debug("target day: " . $target_day->format(\DateTimeInterface::ATOM));
        
        if ($target_day->format("Y") != $this->this_year->format("Y")) {
            Logging::debug("The year is mismatched");
            return null;
        }
        
        $index = intval($target_day->format("z"));
        if (!isset($this->holidays[$index]))
            return null;
        
        return match($this->holidays[$index]->type) {
            DayType::NationalHoliday, DayType::WorkHoliday, DayType::SubstituteHoliday, DayType::SandwichHoliday
                => $this->holidays[$index],
            default => null
        };
    }
    
    public function calculateVernalEquinoxDay(): \DateTimeImmutable
    {
        // equation stolen from https://www.hesperus.net/other/equinox.aspx
        $year = intval($this->this_year->format("Y"));
        Logging::debug("this year: " . $year);
        $day = intval(0.242385544201545 * $year - (intdiv($year, 4) - intdiv($year, 100) + intdiv($year, 400)) + 20.9150411785049);
        Logging::debug("calculated vernal equinox day: " . $day);
        return $this->this_year->modify("3/{$day}");
    }
    
    public function calculateAutumnalEquinoxDay(): \DateTimeImmutable
    {
        // equation stolen from https://www.hesperus.net/other/equinox.aspx
        $year = intval($this->this_year->format("Y"));
        Logging::debug("this year: " . $year);
        $day = intval(0.242035499172366 * $year - (intdiv($year, 4) - intdiv($year, 100) + intdiv($year, 400)) + 24.0227494548387);
        Logging::debug("calculated autumnal equinox day: " . $day);
        return $this->this_year->modify("9/{$day}");
    }
    
    public function getSubstituteHolidayOverflow(): array
    {
        return $this->substitution_overflown;
    }
}