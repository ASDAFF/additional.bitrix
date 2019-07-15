<?php

namespace InetSys\Main;

use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\UserTable;
use CUser;
use InetSys\Constructor\EntityConstructor;

class UserHelper
{
    /**
     * Проверяет вхождение пользователя в группу
     *
     * @param string $groupStringId
     * @param int    $userId
     *
     * @return bool
     * @throws \InetSys\User\Exception\GroupNotFoundException
     */
    public static function isInGroup($groupStringId, $userId)
    {
        $userId = (int)$userId;
        $groupStringId = trim($groupStringId);

        if ($userId <= 0 || empty($groupStringId)) {
            return false;
        }

        return in_array(
            UserGroupHelper::getGroupIdByCode($groupStringId),
            CUser::GetUserGroup($userId),
            false
        );
    }

    /**
     * Возвращает логин пользователя по хешу его запомненной авторизации.
     *
     * @param string $hash
     *
     * @return string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getLoginByHash($hash)
    {
        $hash = trim($hash);
        if (empty($hash)) {
            return '';
        }

        $dataManager = EntityConstructor::compileEntityDataClass('UserStoredAuth', 'b_user_stored_auth');
        $result = $dataManager::query()
            ->setSelect(['LOGIN'])
            ->setFilter(['=STORED_HASH' => $hash])
            ->registerRuntimeField(new ReferenceField('USER', UserTable::class, ['=this.USER_ID' => 'ref.ID']))
            ->exec()->fetch();
//        $query =
//            'SELECT LOGIN ' .
//            'FROM b_user_stored_auth as USA ' .
//            'INNER JOIN b_user as U ' .
//            'ON USA.USER_ID = U.ID ' .
//            'WHERE USA.STORED_HASH = \'' . $hash . '\'';
//
//        $result = Application::getConnection()->query($query)->fetch();

        if (false === $result || !isset($result['LOGIN'])) {
            return '';
        }

        return trim($result['LOGIN']);
    }

    /**
     * @param $arUser
     *
     * @return string
     */
    public static function getFullNameByData($arUser)
    {
        if (!empty($arUser['LAST_NAME']) && !empty($arUser['NAME'])) {
            $fullName = $arUser['LAST_NAME'] . ' ' . $arUser['NAME'];
        } elseif (!empty($arUser['NAME']) && !empty($arUser['SECOND_NAME'])) {
            $fullName = $arUser['NAME'] . ' ' . $arUser['SECOND_NAME'];
        } elseif (!empty($arUser['NAME'])) {
            $fullName = $arUser['NAME'];
        } else {
            $fullName = $arUser['LOGIN'];
        }

        return $fullName;
    }
}
