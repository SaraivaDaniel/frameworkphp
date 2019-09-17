<?php

namespace Saraiva\Framework\View\Twig\Filter;

use Saraiva\Framework\Helper\CpfCnpjHelper;

class maskcpfcnpj {

    public static function maskcpfcnpj($input) {
        $string = preg_replace('/[^0-9]/', '', $input);

        if (strlen($string) == 11) {
            // cpf
            return CpfCnpjHelper::mascaraCPF($string);
        } elseif (strlen($string) == 14) {
            // cnpj
            return CpfCnpjHelper::mascaraCNPJ($string);
        } else {
            // nenhum dos dois, retorna sem mascara
            return $string;
        }
    }

}
