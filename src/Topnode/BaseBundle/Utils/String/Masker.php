<?php

namespace App\Topnode\BaseBundle\Utils\String;

class Masker
{
    public static function percent($value, int $precision = 3, $decimalPoint = ',', $thousandsSeparator = '.'): string
    {
        return number_format($value, $precision, $decimalPoint, $thousandsSeparator) . '%';
    }

    public static function moneyBRL($value, int $precision = 2): string
    {
        return 'R$ ' . number_format($value, $precision, ',', '.');
    }

    public static function phoneNumberBR($value): string
    {
        if (10 == strlen($value)) {
            return self::mask('(##) ####-####', $value);
        }

        if (11 == strlen($value)) {
            return self::mask('(##) #####-####', $value);
        }

        return $value;
    }

    public static function cpf($value): string
    {
        if (11 == strlen($value)) {
            return self::mask('###.###.###-##', $value);
        }

        return $value;
    }

    public static function cnpj($value): string
    {
        if (14 == strlen($value)) {
            return self::mask('##.###.###-####/##', $value);
        }

        if (15 == strlen($value)) {
            return self::mask('###.###.###-####/##', $value);
        }

        return $value;
    }

    public static function mask(string $mask, s $value): string
    {
        $value = str_replace(' ', '', $value);
        $strlen = strlen($value);

        for ($i = 0; $i < $strlen; ++$i) {
            if (false !== strpos($mask, '#') && isset($mask[strpos($mask, '#')])) {
                $mask[strpos($mask, '#')] = $value[$i];
            }
        }

        return $mask;
    }
}
