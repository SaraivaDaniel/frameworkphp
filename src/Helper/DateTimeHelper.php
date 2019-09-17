<?php

namespace Saraiva\Framework\Helper;

use Carbon\Carbon;

class DateTimeHelper {

    /**
     * 
     * @param string $date
     * @return int
     */
    static function mysqlToTimestamp($date) {
        $dt = Carbon::createFromFormat('Y-m-d H:i:s', $date);
        return $dt->getTimestamp();
    }
    
    /**
     * 
     * @param string $date
     * @param string $format
     * @return Carbon|bool
     */
    public static function dateCreateFromFormat($date, $format = 'd/m/Y') {
        $dt = Carbon::createFromFormat($format . ' H:i:s', $date . ' 00:00:00');
        if (FALSE == ($dt && $dt->format($format) == $date)) {
            return FALSE;
        } else {
            return $dt;
        }
    }

}
