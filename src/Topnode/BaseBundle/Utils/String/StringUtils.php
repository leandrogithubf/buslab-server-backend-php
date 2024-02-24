<?php

namespace App\Topnode\BaseBundle\Utils\String;

class StringUtils
{
    public static function slugify(string $string): string
    {
        $string = self::normalize($string);
        $string = iconv('utf-8', 'us-ascii//TRANSLIT', $string);
        $string = self::onlyAlphaNum($string);
        $string = strtolower($string);
        $string = preg_replace('/\s/', '-', $string);

        return preg_replace('~-+~', '-', $string);
    }

    public static function normalize(string $string): string
    {
        return filter_var(
            self::removeMultipleWhiteSpaces($string),
            FILTER_SANITIZE_STRING
        );
    }

    public static function removeMultipleWhiteSpaces(string $string, bool $trim = true): string
    {
        $string = preg_replace('/\s+/', ' ', $string);

        return $trim ? trim($string) : $string;
    }

    public static function onlyAlphabetical(string $string, bool $keepSpaces = true): string
    {
        return preg_replace('/[^A-Za-z' . ($keepSpaces ? '\s' : '') . ']/', '', $string);
    }

    public static function onlyAlphaNum(string $string, bool $keepSpaces = true): string
    {
        return preg_replace('/[^A-Za-z0-9' . ($keepSpaces ? '\s' : '') . ']/', '', $string);
    }

    public static function onlyNumbers(string $string): string
    {
        return preg_replace("/\D/", '', $string);
    }
}
