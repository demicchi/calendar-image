<?php

namespace StudioDemmys\CalendarImage;

class Holiday
{
    public function __construct(
        public \DateTimeImmutable $date,
        public DayType $type,
        public ?string $name = null,
        public ?Color $color = null,
    ){}
}