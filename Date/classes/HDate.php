<?php namespace EC\Date;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class HDate
{

    const Span_Second = 1;
    const Span_Minute = 60;
    const Span_Hour = 60 * self::Span_Minute;
    const Span_Day = 24 * self::Span_Hour;
    const Span_Year = 365 * self::Span_Day;


    static private $UTCOffset = 0;

    static public function ExcelToTime($str)
    {
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

    static public function GetDay($time)
    {
        return self::GetDay_UTC($time) - self::$UTCOffset * self::Span_Hour;
    }

    static public function GetDay_UTC($time)
    {
        $time  = floor($time / self::Span_Day) * self::Span_Day;

        return $time;
    }

    static public function GetMonthName($monthNr)
    {
        return EC\HText::_('Date:monthNames_' . $monthNr);
    }

    static public function GetTimezoneOffset($timezone_name)
    {
        $utc_time = new \DateTime('now', new \DateTimeZone('UTC'));

        $timezone = new \DateTimeZone($timezone_name);
        $timezone_offset = $timezone->getOffset($utc_time) / 60 / 60;

        return $timezone_offset;
    }

    static public function Format_Date($time)
    {
        if ($time === null)
            return $time;

        return gmdate(EC\HText::_('Date:format_Date'), $time);
    }

    static public function Format_DateTime($time)
    {
        if ($time === null)
            return $time;

        return gmdate(EC\HText::_('Date:format_DateTime'), $time);
    }

    static public function Format_Time($time)
    {
        if ($time === null)
            return $time;

        return gmdate(EC\HText::_('Date:format_Time'), $time);
    }

    static public function Format_DayOfWeek($time)
    {
        $day_of_week = gmdate('l', $time);
        return EC\HText::_("Date:format_DayOfWeek_{$day_of_week}");
    }

    static public function GetTime()
    {
        return time();
    }

    static public function GetTime_Rel()
    {
        return time() + self::GetUTCOffset() * self::Span_Hour;
    }

    static public function GetUTCOffset()
    {
        return self::$UTCOffset;
    }

    static public function Round_Day($time)
    {
        if ($time === null)
            return $time;

        return floor($time / self::Span_Day) * self::Span_Day;
    }

    static public function SetTimezoneOffset($timezone_name)
    {
        EC\HDate::SetUTCOffset(self::GetTimezoneOffset($timezone_name));
    }

    static public function SetUTCOffset($utc_offset)
    {
        self::$UTCOffset = $utc_offset;
    }

    static public function StrToTime($str)
    {
        if ($str === null || $str === '')
            return null;

        return strtotime($str . ' UTC');
    }

}
