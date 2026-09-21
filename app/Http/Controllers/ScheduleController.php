<?php

namespace App\Http\Controllers;

use App\Support\MonthlySchedule;
use App\Support\Timezone;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function __invoke(Request $request, MonthlySchedule $schedule): View
    {
        $now = Timezone::nowLocal();

        $year = (int) $request->integer('year', $now->year);
        if ($year < 2000 || $year > 2100) {
            $year = $now->year;
        }

        $month = (int) $request->integer('month', $now->month);
        if ($month < 1 || $month > 12) {
            $month = $now->month;
        }

        $view = in_array($request->string('view')->toString(), ['calendar', 'list'], true)
            ? $request->string('view')->toString()
            : 'calendar';

        return view('schedule.index', $schedule->build($year, $month) + ['initialView' => $view]);
    }
}
