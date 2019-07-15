<?php

namespace InetSys\HLBlock;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Loader;
use Exception;
use LogicException;
use RuntimeException;

class HLBlockFactory
{
    /**
     * Возвращает скомпилированную сущность HL-блока по имени его сущности.
     *
     * @param string $hlBlockName
     *
     * @return DataManager
     * @throws Exception
     */
    public static function createTableObject($hlBlockName)
    {
        return self::doCreateTableObject(['=NAME' => $hlBlockName]);
    }

    /**
     * Возвращает скомпилированную сущность HL-блока по имени его таблицы в базе данных.
     *
     * @param string $tableName
     *
     * @return DataManager
     * @throws Exception
     */
    public static function createTableObjectByTable($tableName)
    {
        return self::doCreateTableObject(['=TABLE_NAME' => $tableName]);
    }

    /**
     * Возвращает скомпилированную сущность HL-блока по заданному фильтру, но фильтр должен в итоге находить один
     * HL-блок.
     *
     * @param array $filter
     *
     * @return DataManager
     * @throws Exception
     */
    private static function doCreateTableObject(array $filter)
    {
        Loader::includeModule('highloadblock');

        $result = (new Query(HighloadBlockTable::getEntity()))
            ->setFilter($filter)
            ->setSelect(['*'])
            ->exec();

        if ($result->getSelectedRowsCount() > 1) {
            throw new LogicException('Неверный фильтр: найдено несколько HLBlock.');
        }

        $hlBlockFields = $result->fetch();

        if (!is_array($hlBlockFields)) {
            throw new Exception('HLBlock не найден.');
        }

        $dataManager = HighloadBlockTable::compileEntity($hlBlockFields)->getDataClass();

        if (is_string($dataManager)) {

            return new $dataManager;

        } elseif (is_object($dataManager)) {

            return $dataManager;

        }

        throw new RuntimeException('Ошибка компиляции сущности для HLBlock.');
    }
}
