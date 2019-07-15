<?php

namespace InetSys\Helpers;

class WordHelper
{
    /**
     * Возвращает нужную форму существительного, стоящего после числительного
     *
     * @param int   $number числительное
     * @param array $forms  формы слова для 1, 2, 5. Напр. ['дверь', 'двери', 'дверей']
     *
     * @return mixed
     */
    public static function declension($number, array $forms)
    {
        $ar = [2, 0, 1, 1, 1, 2];
        $key = ($number % 100 > 4 && $number % 100 < 20) ? 2 : $ar[min($number % 10, 5)];

        return $forms[$key];
    }

    /**
     * Возвращает отформатированный вес
     *
     * @param float $weight
     * @param bool  $short
     *
     * @param int   $fullLimit
     *
     * @return string
     */
    public static function showWeight($weight, $short = false, $fullLimit = 0)
    {
        if ($short && ($fullLimit === 0 || ($fullLimit > 0 && $weight > $fullLimit))) {
            return static::numberFormat($weight / 1000, 2, true) . ' кг';
        }

        $parts = [];

        $kg = floor($weight / 1000);
        if ($kg) {
            $parts[] = static::numberFormat($kg, 0) . ' кг';
        }

        $g = $weight % 1000;
        if ($g) {
            $parts[] = $g . ' г';
        }

        return implode(' ', $parts);
    }

    /**
     * Возвращает отформатированную длину в см - задается в мм
     *
     * @param float $lengthMm - длинна в миллиметрах
     *
     * @return string
     */
    public static function showLengthByMillimeters($lengthMm)
    {
        return static::numberFormat($lengthMm / 10, 1, true) . ' см';
    }

    /**
     * Форматированный вывод чиел, с возможностью удаления незначащих нулей и с округлением до нужной точности
     *
     * @param      $number
     * @param int  $decimals
     *
     * @param bool $delEndNull
     *
     * @return string
     */
    public static function numberFormat($number, $decimals = 2, $delEndNull = false)
    {
        $number = number_format($number, $decimals, '.', ' ');
        if ($delEndNull) {
            $number = rtrim($number, '0');
            $number = rtrim($number, '.');
        }
        return $number;
    }

    /**
     * Очистка текста от примесей(тегов, лишних спец. символов)
     *
     * @param $string
     *
     * @return mixed
     */
    public static function clear($string)
    {
        return str_replace(["\r", PHP_EOL], '', strip_tags(html_entity_decode($string)));
    }
}
