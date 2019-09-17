<?php

namespace Saraiva\Framework\Helper;

use Carbon\Carbon;
use Exception;

class FeriadoHelper {
    
    static $feriadosPorAno = array();
    static $file = '';

    public static function setFile($file) {
        static::$file = $file;
    }
    
    public static function feriadosPorAno($ano) {
        if (isset(static::$feriadosPorAno[$ano])) {
            return static::$feriadosPorAno[$ano];
        }
        
        // carrega arquivo
        if (!file_exists(static::$file)) {
            throw new Exception("Arquivo de feriados não localizado");
        }
        
        $json = file_get_contents(static::$file);
        $feriados = json_decode($json, TRUE);
        
        if (!isset($feriados[$ano])) {
            throw new Exception("Feriados para o ano $ano não configurados");
        }
        
        static::$feriadosPorAno[$ano] = $feriados[$ano];

        return $feriados[$ano];
    }
    
    public static function eFeriado(Carbon $data) {
        $feriados = static::feriadosPorAno($data->year);
        $dia = $data->format('d/m');
        
        if (in_array($dia, $feriados)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
}