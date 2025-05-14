<?php namespace EC\Date;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class HDate {

    const Span_Second = 1;
    const Span_Minute = 60;
    const Span_Hour = 60 * self::Span_Minute;
    const Span_Day = 24 * self::Span_Hour;
    const Span_Year = 365 * self::Span_Day;

    const Millis_Span_Second = 1000;
    const Millis_Span_Minute = 60 * self::Millis_Span_Second;
    const Millis_Span_Hour = 60 * self::Millis_Span_Minute;
    const Millis_Span_Day = 24 * self::Millis_Span_Hour;
    const Millis_Span_Year = 365 * self::Millis_Span_Day;


    static private $TimeZone = null;

    static public function ExcelToTime($str) {
        if ($str === null || $str === '')
            return null;

        if (!is_numeric($str))
            return null;

        /* Modify by (1970-01-01 - 1900-01-01) days difference. */
        $modificator = 25567;
        $diff = (intval($str) - $modificator - 2) * self::Span_Day;
        $start = strtotime("1970-01-01 UTC");

        return ($start + $diff);
    }

    static public function GetDay($time = null) {
        if ($time === null)
            $time = self::GetTime();

        return self::GetDay_UTC($time + self::GetUTCOffset_Time($time)) -
                self::GetUTCOffset_Time($time);
    }

    static public function GetDay_UTC($time = null) {
        if ($time === null)
            $time = self::GetTime();

        $time  = floor($time / self::Span_Day) * self::Span_Day;

        return $time;
    }

    static public function GetMonthName($monthNr) {
        return EC\HText::_('Date:monthNames_' . $monthNr);
    }

    static public function GetTimeZoneOffset($timezone_name) {
        $utc_time = new \DateTime('now', new \DateTimeZone('UTC'));

        $timezone = new \DateTimeZone($timezone_name);
        $timezone_offset = $timezone->getOffset($utc_time) / 60 / 60;

        return $timezone_offset;
    }

    static public function Format_Date($time) {
        if ($time === null)
            return '-';

        $time += self::GetUTCOffset_Time($time);

        return gmdate(EC\HText::_('Date:format_Date'), $time);
    }

    static public function Format_Date_UTC($time) {
        if ($time === null)
            return '-';

        return gmdate(EC\HText::_('Date:format_Date'), $time);
    }

    static public function Format_Date_Rel($time) {
        if ($time === null)
            return '-';

        return gmdate(EC\HText::_('Date:format_Date'), $time);
    }

    static public function Format_DateTime($time) {
        if ($time === null)
            return '-';

        $time += self::GetUTCOffset_Time($time);

        return gmdate(EC\HText::_('Date:format_DateTime'), $time);
    }

    static public function Format_DateTime_UTC($time) {
        if ($time === null)
            return '-';

        return gmdate(EC\HText::_('Date:format_DateTime'), $time);
    }

    static public function Format_Time($time) {
        if ($time === null)
            return '-';

        return gmdate(EC\HText::_('Date:format_Time'), $time);
    }

    static public function Format_Time_Rel($time) {
        if ($time === null)
            return '-';

        return gmdate(EC\HText::_('Date:format_Time'), $time);
    }

    static public function Format_DayOfWeek($time) {
        if ($time === null)
            return '-';

        $day_of_week = gmdate('l', $time);
        return EC\HText::_("Date:format_DayOfWeek_{$day_of_week}");
    }

    static public function GetTime(): ?float {
        return time();
    }

    static public function GetTimeMillis(): ?float {
        return round(microtime(true) * 1000);
    }

    static public function GetTime_Rel(?float $time = null): ?float {
        if ($time === null)
            $time = time();

        return $time + self::GetUTCOffset($time) * self::Span_Hour;
    }

    static public function GetTime_RelNeg(?float $time = null) {
        if ($time === null)
            $time = time();

        return $time - self::GetUTCOffset($time) * self::Span_Hour;
    }

    static public function GetTimeZone(): \DateTimeZone {
        if (self::$TimeZone == null)
            return new \DateTimeZone('UTC');

        return self::$TimeZone;
    }

    static public function GetUTCOffset(float $time) {
        self::GetUTCOffset_Time($time) / 60 / 60;
    }

    static public function GetUTCOffset_Time(float $time) {
        $dateTime = new \DateTime();
        $dateTime->setTimestamp($time);
        
        return self::GetTimeZone()->getOffset($dateTime);
    }

    static public function Round_Day($time) {
        if ($time === null)
            return $time;

        return floor($time / self::Span_Day) * self::Span_Day;
    }

    static public function SetTimeZone(string $timeZoneName) {
        self::$TimeZone = new \DateTimeZone($timeZoneName);
    }

    static public function StrToTime($str) {
        if ($str === null || $str === '')
            return null;

        return (float)strtotime($str . ' UTC');
    }

}
