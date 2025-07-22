<?php

namespace App\Service;

use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventDateTime;

class GoogleCalendar
{
    public static function createClient($accessToken)
    {
        $client = new Google_Client();
        $client->setAccessToken($accessToken);
        $client->addScope(Google_Service_Calendar::CALENDAR);
        return new Google_Service_Calendar($client);
    }

    public static function createRecurringEvent($accessToken, $summary, $start, $frequency, $byDay = null, $byMonthDay = null, $byMonth = null)
    {
        $calendarService = self::createClient($accessToken);

        $event = new Google_Service_Calendar_Event([
            'summary' => $summary,
            'start' => [
                'dateTime' => $start,
                'timeZone' => 'Asia/Jakarta',
            ],
            'end' => [
                'dateTime' => now()->parse($start)->addHour()->toRfc3339String(),
                'timeZone' => 'Asia/Jakarta',
            ],
            'recurrence' => [
                self::buildRecurrenceRule($frequency, $byDay, $byMonthDay, $byMonth)
            ]
        ]);

        return $calendarService->events->insert('primary', $event);
    }

    protected static function buildRecurrenceRule($frequency, $byDay = null, $byMonthDay = null, $byMonth = null)
    {
        $rule = "RRULE:FREQ=$frequency";

        if ($byDay) {
            $rule .= ";BYDAY=$byDay";
        }

        if ($byMonthDay) {
            $rule .= ";BYMONTHDAY=$byMonthDay";
        }

        if ($byMonth) {
            $rule .= ";BYMONTH=$byMonth";
        }

        return $rule;
    }
}
