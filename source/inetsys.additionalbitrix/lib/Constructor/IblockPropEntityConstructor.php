<?php

namespace InetSys\Constructor;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\SystemException;

class IblockPropEntityConstructor extends EntityConstructor
{
    /**
     * @param int $iblockId
     *
     * @return DataManager|string
     * @throws SystemException
     */
    public static function getDataClass($iblockId)
    {
        $className = 'ElementPropS' . $iblockId;
        $tableName = 'b_iblock_element_prop_s' . $iblockId;
        return parent::compileEntityDataClass($className, $tableName);
    }
}