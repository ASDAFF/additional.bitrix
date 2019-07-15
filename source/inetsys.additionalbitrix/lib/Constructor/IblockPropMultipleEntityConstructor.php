<?php

namespace InetSys\Constructor;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\SystemException;

class IblockPropMultipleEntityConstructor extends EntityConstructor
{
    /**
     * @param int $iblockId
     *
     * @return DataManager|string
     * @throws SystemException
     */
    public static function getDataClass($iblockId)
    {
        $className = 'ElementPropM' . $iblockId;
        $tableName = 'b_iblock_element_prop_m' . $iblockId;
        return parent::compileEntityDataClass($className, $tableName);
    }
}