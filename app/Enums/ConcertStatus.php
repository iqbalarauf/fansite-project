<?php

namespace App\Enums;

enum ConcertStatus: string
{
    case OffAir = 'off-air';
    case OnAir = 'on-air';
    case Jkt48Event = 'jkt48-event';
    case Media = 'media';
    case OfcEvent = 'ofc-event';
    case Brand = 'brand';
}
