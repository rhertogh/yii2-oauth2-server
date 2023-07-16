<?php

namespace rhertogh\Yii2Oauth2Server\helpers;

class DateIntervalHelper
{
    /**
     * @param \DateInterval|null $interval
     * @return string|null
     */
    public static function toString($interval) {

        if ($interval === null) {
            return null;
        }

        $dateParts = array_filter([
            'Y' => $interval->y,
            'M' => $interval->m,
            'D' => $interval->d,
        ]);
        $result = 'P' . implode(array_map(fn($v, $k) => $v . $k, $dateParts, array_keys($dateParts)));

        $timeParts = array_filter([
            'H' => $interval->h,
            'M' => $interval->i,
            'S' => $interval->s,
            'F' => $interval->f,
        ]);
        if ($timeParts) {
            $result .= 'T' . implode(array_map(fn($v, $k) => $v . $k, $timeParts, array_keys($timeParts)));
        }

        return $result;
    }
}
