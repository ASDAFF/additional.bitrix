<? namespace InetSys\Additional;

use Bitrix\Main\Entity;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use InetSys\Helpers\TaggedCacheHelper;

//use Bitrix\Main;

Loc::loadMessages(__FILE__);

if (!Loader::includeModule('highloadblock')) {
    global $APPLICATION;
    $APPLICATION->ThrowException(Loc::getMessage("NOT_INCLUDE_HL_BLOCK"));
}

//$eventManager->registerEventHandler();

/**
 * Class HLBlockEvents
 * @package InetSys\Additional
 */
class HLBlockEvents
{
    protected static $instance;

    /**
     * @return mixed
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
     * @param Entity\Event $event
     */
    public function onAfterChangeItem(Entity\Event $event)
    {
        $arParams = $event->getParameters();

        TaggedCacheHelper::clearManagedCache(['hl_block_' . $arParams['HLBLOCK_ID']]);
    }

    /**
     * @param Entity\Event $event
     */
    public function onAfterChangeColumn(Entity\Event $event)
    {
        $arParams = $event->getParameters();

        TaggedCacheHelper::clearManagedCache(['hl_block_' . $arParams['IBLOCK_ID'] . '_fields']);
    }
}