<?php

namespace Saraiva\Framework\Locale;

use Exception;

class Countries {
    
    public static function getCountryCodes($file) {
        $fh = fopen($file, 'r');
        if ($fh === FALSE) {
            throw new Exception("Erro ao abrir arquivo de países");
        }
        $countries = [];
        while ($line = fgetcsv($fh, 0, ";")) {
            $countries[$line[2]] = $line[1];
        }
        fclose($fh);
        
        return $countries;
    }

}
