<?php

namespace PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel;

use Exception;
use PhpOffice\PhpSpreadsheet\Calculation\Functions;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class Second
{
    /**
     * MINUTE.
     *
     * Returns the minutes of a time value.
     * The minute is given as an integer, ranging from 0 to 59.
     *
     * Excel Function:
     *        MINUTE(timeValue)
     *
     * @param mixed $timeValue Excel date serial value (float), PHP date timestamp (integer),
     *                                    PHP DateTime object, or a standard time string
     *
     * @return int|string Minute
     */
    public static function evaluate($timeValue)
    {
        try {
            $timeValue = Functions::flattenSingleValue($timeValue);
            Helpers::nullFalseTrueToNumber($timeValue);
            if (!is_numeric($timeValue)) {
                $timeValue = Helpers::getTimeValue($timeValue);
            }
            Helpers::validateNotNegative($timeValue);
        } catch (Exception $e) {
            return $e->getMessage();
        }

        // Execute function
        $timeValue = fmod($timeValue, 1);
        $timeValue = Date::excelToDateTimeObject($timeValue);

        return (int) $timeValue->format('s');
    }
}
