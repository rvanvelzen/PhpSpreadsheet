<?php

namespace PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel;

use Exception;
use PhpOffice\PhpSpreadsheet\Calculation\Functions;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class NetworkDays
{
    /**
     * NETWORKDAYS.
     *
     * Returns the number of whole working days between start_date and end_date. Working days
     * exclude weekends and any dates identified in holidays.
     * Use NETWORKDAYS to calculate employee benefits that accrue based on the number of days
     * worked during a specific term.
     *
     * Excel Function:
     *        NETWORKDAYS(startDate,endDate[,holidays[,holiday[,...]]])
     *
     * @param mixed $startDate Excel date serial value (float), PHP date timestamp (integer),
     *                                            PHP DateTime object, or a standard date string
     * @param mixed $endDate Excel date serial value (float), PHP date timestamp (integer),
     *                                            PHP DateTime object, or a standard date string
     * @param mixed $dateArgs
     *
     * @return int|string Interval between the dates
     */
    public static function evaluate($startDate, $endDate, ...$dateArgs)
    {
        try {
            //    Retrieve the mandatory start and end date that are referenced in the function definition
            $sDate = Helpers::getDateValue($startDate);
            $eDate = Helpers::getDateValue($endDate);
            $startDate = min($sDate, $eDate);
            $endDate = max($sDate, $eDate);
            //    Get the optional days
            $dateArgs = Functions::flattenArray($dateArgs);
            //    Test any extra holiday parameters
            $holidayArray = [];
            foreach ($dateArgs as $holidayDate) {
                $holidayArray[] = Helpers::getDateValue($holidayDate);
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }

        // Execute function
        $startDow = self::calcStartDow($startDate);
        $endDow = self::calcEndDow($endDate);
        $wholeWeekDays = (int) floor(($endDate - $startDate) / 7) * 5;
        $partWeekDays = self::calcPartWeekDays($startDow, $endDow);

        //    Test any extra holiday parameters
        $holidayCountedArray = [];
        foreach ($holidayArray as $holidayDate) {
            if (($holidayDate >= $startDate) && ($holidayDate <= $endDate)) {
                if ((WeekDay::evaluate($holidayDate, 2) < 6) && (!in_array($holidayDate, $holidayCountedArray))) {
                    --$partWeekDays;
                    $holidayCountedArray[] = $holidayDate;
                }
            }
        }

        return self::applySign($wholeWeekDays + $partWeekDays, $sDate, $eDate);
    }

    private static function calcStartDow(float $startDate): int
    {
        $startDow = 6 - (int) WeekDay::evaluate($startDate, 2);
        if ($startDow < 0) {
            $startDow = 5;
        }

        return $startDow;
    }

    private static function calcEndDow(float $endDate): int
    {
        $endDow = (int) WeekDay::evaluate($endDate, 2);
        if ($endDow >= 6) {
            $endDow = 0;
        }

        return $endDow;
    }

    private static function calcPartWeekDays(int $startDow, int $endDow): int
    {
        $partWeekDays = $endDow + $startDow;
        if ($partWeekDays > 5) {
            $partWeekDays -= 5;
        }

        return $partWeekDays;
    }

    private static function applySign(int $result, float $sDate, float $eDate): int
    {
        return ($sDate > $eDate) ? -$result : $result;
    }
}
