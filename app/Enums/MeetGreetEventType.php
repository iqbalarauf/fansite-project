<?php

namespace App\Enums;

enum MeetGreetEventType: string
{
    case MeetGreet = 'meet-greet';
    case VideoCall = 'video-call';
}
