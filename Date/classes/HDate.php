<?php namespace EC\Date;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class HDate
{

    const Span_Hour = 3600;
    const Span_Day = 86400;
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
        $utc_time = new \DateTime('now', new \DateTimeZone('UTC'));

        $timezone = new \DateTimeZone($timezone_name);
        $timezone_offset = $timezone->getOffset($utc_time) / 60 / 60;

        EC\HDate::SetUTCOffset($timezone_offset);
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

    static public function Time()
    {
        return time() + self::GetUTCOffset();
    }

}
