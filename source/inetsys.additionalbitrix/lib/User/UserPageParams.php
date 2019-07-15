<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 07.09.18
 * Time: 13:39
 */

namespace InetSys\User;


use InetSys\Main\UserHelper;
use InetSys\MiscUtils;

class UserPageParams
{
    protected static $instance;
    protected $arCurUser = [];

    /**
     * @return static
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }

        return self::$instance;
    }

    /**
     * @param bool $arGroups
     * @param bool $bAddUserGroups
     */
    public function setCurrentUser($arGroups = false, $bAddUserGroups = false)
    {
        global $USER;
        $this->arCurUser = [];
        $this->arCurUser['AUTH'] = $USER->IsAuthorized();
        if ($this->arCurUser['AUTH']) {
            $this->arCurUser['ID'] = \CUser::GetID();
            $arFilter = ['ID' => $this->arCurUser['ID']];
            $arUserParams = [
                'SELECT' => ['UF_MANAGER'],
                'FIELDS' => ['ID', 'LAST_NAME', 'NAME', 'SECOND_NAME', 'LOGIN'],
            ];
            $arUser = \CUser::GetList($by, $order, $arFilter, $arUserParams)->Fetch();
            $this->arCurUser['MANAGER'] = $arUser['UF_MANAGER'];
            MiscUtils::trimArrayStrings($arUser);
            $this->arCurUser['FULL_NAME'] = UserHelper::getFullNameByData($arUser);
        }
        if ($arGroups != false && is_array($arGroups) && !empty($arGroups)) {
            if ($this->arCurUser['AUTH']) {
                $user_groups = \CUser::GetUserGroupArray();
                if ($bAddUserGroups) {
                    $this->arCurUser['GROUPS'] = $user_groups;
                }
                foreach ($arGroups as $arGroup) {
                    $this->arCurUser[$arGroup['NAME']] = false;
                    if (in_array($arGroup['ID'], $user_groups)) {
                        $this->arCurUser[$arGroup['NAME']] = true;
                    }
                }
            } else {
                foreach ($arGroups as $arGroup) {
                    if ($arGroup['DEFAULT'] == 'Y') {
                        $this->arCurUser[$arGroup['NAME']] = true;
                        break;
                    }
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getCurUser()
    {
        return $this->arCurUser;
    }
}