<?php

namespace Saraiva\Framework\Helper;

class InputHelper {
    
    static function inputOrDefault($input, $var, $default = NULL, $emptydefault = FALSE) {
        if (isset($input[$var])) {
            if (empty($input[$var])) {
                if (!$emptydefault) {
                    return $input[$var];
                } else {
                    return $default;
                }
            } else {
                return $input[$var];
            }
        } else {
            return $default;
        }
    }

    static function getOrDefault($var, $default = NULL, $emptydefault = FALSE) {
        return static::inputOrDefault($_GET, $var, $default, $emptydefault);
    }

    static function postOrDefault($var, $default = NULL, $emptydefault = FALSE) {
        return static::inputOrDefault($_POST, $var, $default, $emptydefault);
    }

    static public function cleanText($string, $keep_new_line = FALSE) {
        if ($keep_new_line) {
            $pattern = '/[\x00-\x09\x0B-\x1F';
        } else {
            $pattern = '/[\x00-\x1F';
        }
        $pattern .= '\x80-\x9F';
        $pattern .= '\x7F]/u';

        $string = preg_replace($pattern, '', $string);
        $string = preg_replace('/\xA0/u', ' ', $string);
        $string = trim($string);
        return $string;
    }

    static public function validate_numberformat($input) {
        // trima espaços (seguro ignorar)
        $input = trim($input);

        if ($input == '') {
            return FALSE;
        }

        // retira caracteres estranhos, não esperados
        $clear = preg_replace("/[^-0-9.,]/", "", $input);
        if ($clear !== $input) {
            return FALSE;
        }
        
        // pode ter hifen representando negativo
        $qtde_hifen = substr_count($clear, '-');
        if ($qtde_hifen > 1) {
            return FALSE;
        } elseif ($qtde_hifen === 1) {
            // se tem hifen tem que ser primeiro caractere
            if (strpos($clear, "-") !== 0) {
                return FALSE;
            }
        }

        // pode ter zero ou uma virgula
        $qtde_virgula = substr_count($clear, ',');
        if ($qtde_virgula > 1) {
            return FALSE;
        }

        // verifica se está à direita de todos os pontos        
        $pos_virgula = strpos($clear, ',');
        if ($qtde_virgula == 1) {
            $pos_ultimo_ponto = strrpos($clear, '.');
            if ($pos_virgula < $pos_ultimo_ponto) {
                return FALSE;
            }
        }

        // se tem pelo menos um ponto, deve ter a cada 3 digitos
        $pos_ponto = strpos($clear, ".", 0);
        if ($pos_ponto !== FALSE) {
            if ($pos_virgula !== FALSE) {
                $len = $pos_virgula;
            } else {
                $len = strlen($clear);
            }
            $esquerda = substr($clear, 0, $len);
            $qtde_ponto = substr_count($esquerda, '.');
            $qtde_digitos = strlen($esquerda) - $qtde_ponto;

            // 1,2 => 0       0 >1 :0  (q-1)+(r>1=1) =0
            // 3 => 0         1 =0 :0                
            // 4,5 => 1       1 >1 :1
            // 6 => 1         2 =0 :1
            // 7,8,9 => 2...  2 >1 :2
            $qtde_pontos_necessarios = (intval($qtde_digitos / 3) - 1) + (($qtde_digitos % 3 >= 1) ? 1 : 0);

            if ($qtde_pontos_necessarios !== $qtde_ponto) {
                return FALSE;
            }
        }

        // depois de pontos deve ter 3 digitos e depois de virgula pode ter de 0+ digitos
        $pos_ponto = -1;
        while (FALSE !== $pos_ponto = strpos($clear, '.', $pos_ponto + 1)) {
            $prox3 = substr($clear, $pos_ponto + 1, 3);
            if (strlen($prox3) !== 3 || FALSE === ctype_digit($prox3)) {
                return FALSE;
            }
        }

        $clear = str_replace('.', '', $clear);
        $clear = str_replace(',', '.', $clear);
        return floatval($clear);
    }

}
