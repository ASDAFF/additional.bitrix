<?php

namespace InetSys;

use Bitrix\Main\Type\DateTime;

/**
 * Class MiscTools
 * @package InetSys
 *
 * Все прочие полезные функции, для которых пока нет отдельного класса.
 */
class MiscUtils
{
    /**
     * Возвращает имя класса без namespace
     *
     * @param $object
     *
     * @return string
     */
    public static function getClassName($object)
    {
        $className = get_class($object);
        $pos = strrpos($className, '\\');
        if ($pos) {

            return substr($className, $pos + 1);
        }

        return $pos;
    }

    /**
     * @param $arItems
     */
    public static function trimArrayStrings(&$arItems)
    {
        if (is_array($arItems)) {
            foreach ($arItems as $key => $val) {
                if (is_array($val)) {
                    self::trimArrayStrings($val);
                } else {
                    $arItems[$key] = trim(str_replace(' ', '', $val));
                }
            }
        }
    }

    /**
     * @param     $size
     * @param int $round
     *
     * @return string
     */
    public static function getFormattedSize($size, $round = 2)
    {
        $sizes = ['B', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb', 'Zb', 'Yb'];
        for ($i = 0; $size > 1024 && $i < count($sizes) - 1; $i++) {
            $size /= 1024;
        }

        return round($size, $round) . " " . $sizes[$i];
    }

    /**
     * @param $arr
     */
    public static function eraseArray(&$arr)
    {
        foreach ($arr as $key => $val) {
            if (is_array($val)) {
                self::eraseArray($val);
                if (empty($val)) {
                    unset($arr[$key]);
                }
            }
            if (empty($val)) {
                unset($arr[$key]);
            }
        }
    }

    /**
     * @param array $params
     *
     * @return array|bool|mixed
     */
    public static function getUniqueArray($params = [])
    {
        if (!isset($params['arr1'])) {
            return false;
        }
        if (!isset($params['arr2'])) {
            return $params['arr1'];
        }
        if (!isset($params['bReturnFullDiffArray'])) {
            $params['bReturnFullDiffArray'] = false;
        }
        if (!isset($params['isChild'])) {
            $params['isChild'] = false;
        }
        if (!isset($params['skipKeys'])) {
            $params['skipKeys'] = [];
        }
        $arResult = [];
        if ($params['bReturnFullDiffArray'] && $params['isChild']) {
            $arTmp = [];
            $arDiff = [];
        }
        foreach ($params['arr1'] as $key => $val) {
            if ($params['bReturnFullDiffArray'] && $params['isChild']) {
                $arTmp[$key] = $val;
            }
            if (is_array($val)) {
                if (!in_array($key, $params['skipKeys'])) {
                    if (!isset($params['arr2'][$key]) || (!empty($val) && empty($params['arr2'][$key]))) {
                        if ($params['bReturnFullDiffArray'] && $params['isChild']) {
                            $arDiff[$key] = $val;
                        } else {
                            $arResult[$key] = $val;
                        }
                    } else {
                        $arReturn = self::getUniqueArray(
                            [
                                'arr1'                 => $val,
                                'arr2'                 => $params['arr2'][$key],
                                'bReturnFullDiffArray' => $params['bReturnFullDiffArray'],
                                'skipKeys'             => $params['skipKeys'],
                                'isChild'              => true,
                            ]
                        );
                        if (!empty($arReturn)) {
                            if ($params['bReturnFullDiffArray'] && $params['isChild']) {
                                $arDiff[$key] = $arReturn;
                            } else {
                                $arResult[$key] = $arReturn;
                            }
                        }
                    }
                }
            } else {
                if (!in_array($key, $params['skipKeys'])) {
                    if (!isset($params['arr2'][$key])) {
                        if ($params['bReturnFullDiffArray'] && $params['isChild']) {
                            $arDiff[$key] = $val;
                        } else {
                            $arResult[$key] = $val;
                        }
                    } else {
                        $tmpVal = '0';
                        $tmpArr2Val = '1';
                        if (is_object($val)) {
                            if (is_a($val, 'Bitrix\Main\Type\DateTime')) {
                                /** @var DateTime $val */
                                $tmpVal = $val->format(DateTime::getFormat());
                                /** @var DateTime $val2 */
                                $val2 = $params['arr2'][$key];
                                $tmpArr2Val = $val2->format(DateTime::getFormat());
                                unset($val2);
                            }
                        }
                        if ((!is_object($val) && $val !== $params['arr2'][$key])
                            || (is_object($val) && $tmpVal !== $tmpArr2Val)) {
                            if ($params['bReturnFullDiffArray'] && $params['isChild']) {
                                $arDiff[$key] = $val;
                            } else {
                                $arResult[$key] = $val;
                            }
                        }
                    }
                }
            }
        }
        if (isset($arDiff) && count($arDiff) > 0 && isset($arTmp) && !empty($arTmp)) {
            $arResult = $arTmp;
        }

        return $arResult;
    }
}
