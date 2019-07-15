<?

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

global $DBType;

Loader::registerAutoLoadClasses(
    "inetsys.additionalbitrix",
    [
        //Component
        '\\InetSys\\Component\\BaseBitrixComponent'                   => '/lib/Component/BaseBitrixComponent.php',

        //Connections
        '\\InetSys\\Connections\\Ftp'                                 => '/lib/Connections/Ftp.php',

        //Constructor
        '\\InetSys\\Constructor\\EntityConstructor'                   => '/lib/Constructor/EntityConstructor.php',
        '\\InetSys\\Constructor\\IblockPropEntityConstructor'         => '/lib/Constructor/IblockPropEntityConstructor.php',
        '\\InetSys\\Constructor\\IblockPropMultipleEntityConstructor' => '/lib/Constructor/IblockPropMultipleEntityConstructor.php',

        //Debug
        '\\InetSys\\Debug\\CheckResources'                            => '/lib/Debug/CheckResources.php',
        '\\InetSys\\Debug\\Logger'                                    => '/lib/Debug/Logger.php',

        //Decorators
        '\\InetSys\\Decorators\\FullHrefDecorator'                    => '/lib/Decorators/FullHrefDecorator.php',

        //Form
        '\\InetSys\\Form\\FormHelper'                                 => '/lib/Form/FormHelper.php',

        //Helpers
        '\\InetSys\\Helpers\\ClassFinderHelper'                       => '/lib/Helpers/ClassFinderHelper.php',
        '\\InetSys\\Helpers\\DateHelper'                              => '/lib/Helpers/DateHelper.php',
        '\\InetSys\\Helpers\\MenuHelper'                              => '/lib/Helpers/MenuHelper.php',
        '\\InetSys\\Helpers\\PhoneHelper'                             => '/lib/Helpers/PhoneHelper.php',
        '\\InetSys\\Helpers\\TaggedCacheHelper'                       => '/lib/Helpers/TaggedCacheHelper.php',
        '\\InetSys\\Helpers\\WordHelper'                              => '/lib/Helpers/WordHelper.php',

        //HLBlock
        '\\InetSys\\HLBlock\\HLBlockAdditional'                       => '/lib/HLBlock/HLBlockAdditional.php',
        '\\InetSys\\HLBlock\\HLBlockFactory'                          => '/lib/HLBlock/HLBlockFactory.php',
        '\\InetSys\\HLBlock\\HLBlockHelper'                           => '/lib/HLBlock/HLBlockHelper.php',
        '\\InetSys\\HLBlock\\HLBlockEvents'                           => '/lib/HLBlock/HLBlockEvents.php',

        //Iblock
        '\\InetSys\\Iblock\\IblockHelper'                             => '/lib/Iblock/IblockHelper.php',

        //Mysql
        '\\InetSys\\Mysql\\ExtendsBitrixQuery'                        => '/lib/Mysql/ExtendsBitrixQuery.php',
        '\\InetSys\\Mysql\\MysqlBatchOperations'                      => '/lib/Mysql/MysqlBatchOperations.php',

        //Page
        '\\InetSys\\Page\\PageParams'                                 => '/lib/Page/PageParams.php',

        //User
        '\\InetSys\\User\\UserGroupHelper'                            => '/lib/User/UserGroupHelper.php',
        '\\InetSys\\User\\UserHelper'                                 => '/lib/User/UserHelper.php',
        '\\InetSys\\User\\UserPageParams'                             => '/lib/User/UserPageParams.php',

        //Other
        '\\InetSys\\BitrixUtils'                                      => 'lib/BitrixUtils.php',
        '\\InetSys\\MiscUtils'                                        => 'lib/MiscUtils.php',
    ]
);