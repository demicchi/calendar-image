<?php

namespace StudioDemmys\CalendarImage;

class HolidaySubstitution
{
    public function __construct(
        public \DateTimeImmutable $date,
        public ?array $substitution_avoid = null,
        public ?Color $color = null,
    ){}
}