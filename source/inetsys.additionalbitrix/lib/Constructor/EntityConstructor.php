<?php

namespace InetSys\Constructor;

use Bitrix\Main;
use Bitrix\Main\Entity\DataManager;

class EntityConstructor
{
    /**
     * @param string $className
     * @param string $tableName
     *
     * @return DataManager|string
     * @throws Main\SystemException
     */
    public static function compileEntityDataClass($className, $tableName)
    {
        $entity_data_class = $className;

        if (!preg_match('/^[a-z0-9_]+$/i', $entity_data_class)) {
            throw new Main\SystemException(
                sprintf(
                    'Invalid entity name `%s`.',
                    $entity_data_class
                )
            );
        }

        $entity_data_class .= 'Table';

        if (class_exists($entity_data_class)) {
            return $entity_data_class;
        }

        $eval = '
				class ' . $entity_data_class . ' extends \Bitrix\Main\Entity\DataManager
				{
					public static function getTableName()
					{
						return ' . var_export($tableName, true) . ';
					}

					public static function getMap()
					{
						return ' . var_export(static::getFieldsMap($tableName), true) . ';
					}
				}
			';

        eval($eval);

        return $entity_data_class;
    }

    /**
     * @param $tableName
     *
     * @return array|mixed
     */
    public static function getFieldsMap($tableName)
    {
        $fieldsMap = [];
        $obTable = new \CPerfomanceTable;
        $obTable->Init($tableName);

        $arFields = $obTable->GetTableFields(false, true);

        $arUniqueIndexes = $obTable->GetUniqueIndexes();
        $hasID = false;
        foreach ($arUniqueIndexes as $indexName => $indexColumns) {
            if (array_values($indexColumns) === ['ID']) {
                $hasID = $indexName;
            }
        }

        if ($hasID) {
            $arUniqueIndexes = [$hasID => $arUniqueIndexes[$hasID]];
        }

        if (\is_array($arFields) && !empty($arFields)) {
            foreach ($arFields as $columnName => $columnInfo) {
                if ($columnInfo['orm_type'] === 'boolean') {
                    $columnInfo['nullable'] = true;
                    $columnInfo['type'] = 'bool';
                    $columnInfo['length'] = '';
                    $columnInfo['enum_values'] = [
                        'N',
                        'Y',
                    ];
                }

                if ($columnInfo['type'] === 'int'
                    && ($columnInfo['default'] > 0)
                    && !$columnInfo['nullable']) {
                    $columnInfo['nullable'] = true;
                }

                $match = [];
                if (preg_match('/^(.+)_TYPE$/', $columnName, $match)
                    && array_key_exists($match[1], $arFields)
                    && (int)$columnInfo['length'] === 4) {
                    $columnInfo['nullable'] = true;
                    $columnInfo['orm_type'] = 'enum';
                    $columnInfo['enum_values'] = [
                        'text',
                        'html',
                    ];
                }

                $fieldsMap[$columnName]['data_type'] = $columnInfo['orm_type'];

                $primary = false;
                foreach ($arUniqueIndexes as $indexName => $arColumns) {
                    if (\in_array($columnName, $arColumns, true)) {
                        $fieldsMap[$columnName]['primary'] = true;
                        $primary = true;
                        break;
                    }
                }
                if ($columnInfo['increment']) {
                    $fieldsMap[$columnName]['autocomplete'] = true;
                }
                if (!$primary && $columnInfo['nullable'] === false) {
                    $fieldsMap[$columnName]['required'] = true;
                }
                if ($columnInfo['orm_type'] === 'boolean' || $columnInfo['orm_type'] === 'enum') {
                    $fieldsMap[$columnName]['values'] = $columnInfo['enum_values'];
                }
            }
        }

        return $fieldsMap;
    }
}
