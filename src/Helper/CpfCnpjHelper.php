<?php

namespace Saraiva\Framework\Helper;

use Exception;

class CpfCnpjHelper {

    /**
     * Limpa uma string deixa somente números
     * @param string $c
     * @return string
     */
    static function limpaC($c) {
        return preg_replace('/[^0-9]/', '', $c);
    }

    /**
     * Valida um CPF
     * @param string $cpf com ou sem sinais, deve ter 11 caracteres numéricos [0-9]
     * @return boolean
     */
    static function validaCPF($cpf) {
        $cpf = static::limpaC($cpf);
        if (strlen($cpf) != 11) {
            return FALSE;
        }

        $dvInformado = substr($cpf, 9, 2);
        $digito = array();
        for ($i = 0; $i < 9; $i++) {
            $digito[] = substr($cpf, $i, 1);
        }
        // calculo do 1o digito do DV
        $posicao = 10;
        $soma = 0;
        for ($i = 0; $i < 9; $i++) {
            $soma = $soma + $digito[$i] * $posicao;
            $posicao--;
        }
        $digito[9] = $soma % 11;
        if ($digito[9] < 2) {
            $digito[9] = 0;
        } else {
            $digito[9] = 11 - $digito[9];
        }
        // calculo do 2o digito do DV
        $posicao = 11;
        $soma = 0;
        for ($i = 0; $i < 10; $i++) {
            $soma = $soma + $digito[$i] * $posicao;
            $posicao--;
        }
        $digito[10] = $soma % 11;
        if ($digito[10] < 2) {
            $digito[10] = 0;
        } else {
            $digito[10] = 11 - $digito[10];
        }
        $dv = $digito[9] . $digito[10];
        if ($dv === $dvInformado) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Valida CNPJ
     * @param string $cnpj com ou sem sinais, deve ter 14 caracteres numéricos [0-9]
     * @return boolean
     */
    static function validaCNPJ($cnpj) {
        $cnpj = static::limpaC($cnpj);
        if (strlen($cnpj) != 14) {
            return FALSE;
        }

        $dvInformado = substr($cnpj, 12, 2);
        $digito = array();
        for ($i = 0; $i < 12; $i++) {
            $digito[] = substr($cnpj, $i, 1);
        }
        // calculo do 1o digito verificador
        $str_pesos = '543298765432';
        $pesos = array();
        for ($i = 0; $i < 12; $i++) {
            $pesos[] = substr($str_pesos, $i, 1);
        }
        $soma = 0;
        for ($i = 0; $i < 12; $i++) {
            $soma = $soma + $pesos[$i] * $digito[$i];
        }
        $digito[12] = $soma % 11;
        if ($digito[12] < 2) {
            $digito[12] = 0;
        } else {
            $digito[12] = 11 - $digito[12];
        }
        // calculo do 2o digito verificador
        $str_pesos = '6543298765432';
        $pesos = array();
        for ($i = 0; $i < 13; $i++) {
            $pesos[] = substr($str_pesos, $i, 1);
        }
        $soma = 0;
        for ($i = 0; $i < 13; $i++) {
            $soma = $soma + $pesos[$i] * $digito[$i];
        }
        $digito[13] = $soma % 11;
        if ($digito[13] < 2) {
            $digito[13] = 0;
        } else {
            $digito[13] = 11 - $digito[13];
        }
        $dv = $digito[12] . $digito[13];
        if ($dv === $dvInformado) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    static function mascaraCPF($cpf) {
        $cpf = static::limpaC($cpf);

        if (strlen($cpf) != 11)
            throw new Exception("Valor informado inválido, deve informar um CPF com 11 dígitos");

        $p1 = substr($cpf, 0, 3);
        $p2 = substr($cpf, 3, 3);
        $p3 = substr($cpf, 6, 3);
        $p4 = substr($cpf, 9, 2);

        return "$p1.$p2.$p3-$p4";
    }

    static function mascaraCNPJ($cnpj) {
        $cnpj = static::limpaC($cnpj);

        if (strlen($cnpj) != 14)
            throw new Exception("Valor informado inválido, deve informar um CNPJ com 14 dígitos");

        $p1 = substr($cnpj, 0, 2);
        $p2 = substr($cnpj, 2, 3);
        $p3 = substr($cnpj, 5, 3);
        $p4 = substr($cnpj, 8, 4);
        $p5 = substr($cnpj, 12, 2);

        return "$p1.$p2.$p3/$p4-$p5";
    }

    static function validaDocumento($documento) {
        return (static::validaCPF($documento) || static::validaCNPJ($documento));
    }

}
