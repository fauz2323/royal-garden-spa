<?php

namespace App\Http\Controllers\Api\Users;


use App\Http\Controllers\Controller;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimeSlotController extends Controller
{
    function getTimeSlotList($date)
    {
        $availability = TimeSlot::select([
            'time_slots.time_start',
            'time_slots.time_end',
            DB::raw("
                CASE
                    WHEN COALESCE(global_time_slots.occurrence, 0) < COALESCE(global_time_slots.max_occurrence, 4)
                    THEN TRUE
                    ELSE FALSE
                END as available
            ")
        ])
            ->leftJoin('global_time_slots', function ($join) use ($date) {
                $join->on('time_slots.id', '=', 'global_time_slots.time_slot_id')
                    ->where('global_time_slots.date', '=', $date);
            })
            ->orderBy('time_slots.time_start')
            ->get()
            ->map(function ($slot) {
                $slot->available = (bool) $slot->available;
                return $slot;
            });

        return response()->json([
            'success' => true,
            'data' => $availability
        ]);
    }

}
