<? namespace InetSys\Additional;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Entity\Base;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loader::includeModule('highloadblock');

/**
 * Class HLBlockAdditional
 * @package InetSys\Additional
 * @todo старый интерфейс
 */
Class HLBlockAdditional
{
    protected static $NOT_HL_ID;
    protected static $EMPTY_DATA;
    protected static $EMPTY_ELEMENT_ID;
    protected static $instance = null;
    /**
     * @var bool|DataManager
     */
    protected $entity_data_class;
    /**
     * @var bool|Base
     */
    protected $entity;
    protected $hlblock;
    /**
     * @var bool|Query
     */
    protected $main_query;
    protected $HL_ID;
    protected $errors = [];

    /**
     *
     */
    public function __construct()
    {
        static::$NOT_HL_ID = Loc::getMessage("NOT_INSTALL_HL_ID");
        static::$EMPTY_DATA = Loc::getMessage("EMPTY_DATA_HL");
        static::$EMPTY_ELEMENT_ID = Loc::getMessage("EMPTY_ELEMENT_ID_HL");
        $this->errors = [];
    }

    /**
     * @return static
     */
    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * @param array $params
     *
     * @return array|bool
     * @throws \Bitrix\Main\SystemException
     */
    public function getList($params = [])
    {
        $this->errors = [];

        if ($this->haveValue($params['HL_ID'], 'not') || intval($params['HL_ID']) <= 0) {
            if ($this->haveValue($this->HL_ID, 'not')) {
                $this->errors[] = static::$NOT_HL_ID;
            }
        } else {
            $this->HL_ID = $params['HL_ID'];
        }

        if (!empty($this->errors)) {
            return false;
        }

        if ($this->haveValue($params['bReturnObject'], 'not') || $params['bReturnObject'] !== true) {
            $params['bReturnObject'] = false;
        }

        $arUses = [
            'order'      => false,
            'filter'     => false,
            'select'     => false,
            'groupBy'    => false,
            'navigation' => false,
        ];

        if ($this->haveValue($params['order']) && is_array($params['order'])) {
            $arUses['order'] = true;
        }

        if ($this->haveValue($params['select']) && is_array($params['select'])) {
            $arUses['select'] = true;
        }

        if ($this->haveValue($params['filter']) && is_array($params['filter'])) {
            $arUses['filter'] = true;
        }

        if ($this->haveValue($params['groupBy']) && is_array($params['groupBy'])) {
            $arUses['groupBy'] = true;
        }

        if ($this->haveValue($params['navigation']) && is_array($params['navigation'])) {
            $arUses['navigation'] = true;
        }

        $this->initHLEntity($this->HL_ID, false, true);

        if ($arUses['select']) {
            $this->main_query->setSelect($params['select']);
        } else {
            $this->main_query->setSelect(['*']);
        }

        if ($arUses['order']) {
            $this->main_query->setOrder($params['order']);
        }

        if ($arUses['filter']) {
            $this->main_query->setFilter($params['filter']);
        }

        if ($arUses['groupBy']) {
            $this->main_query->setGroup($params['groupBy']);
        }

        if ($arUses['navigation']) {
            if ($this->haveValue($params['navigation']['nPageTop'])
                && intval($params['navigation']['nPageTop']) > 0
            ) {
                $this->main_query->setLimit($params['navigation']['nPageTop']);
            } elseif ($this->haveValue($params['navigation']['nPageSize'])
                && $this->haveValue(
                    $params['navigation']['iNumPage']
                )
            ) {
                $this->main_query->setLimit($params['navigation']['nPageSize']);
                $this->main_query->setOffset(
                    ($params['navigation']['iNumPage'] - 1) * $params['navigation']['nPageSize']
                );
            }
        }

        $result = $this->main_query->exec();

        $arResult = false;
        while ($row = $result->fetch()) {
            $arResult[] = $row;
        }

        if ($params['bReturnObject']) {
            $arTmp = $arResult;
            $arResult = [];
            $arResult['ITEMS'] = $arTmp;
            $arResult['res'] = &$result;
        }

        return $arResult;
    }

    /**
     * @param            $value
     * @param bool|false $not
     *
     * @return bool
     */
    public function haveValue($value, $not = false)
    {
        if ($not === false) {
            if (isset($value) && !empty($value)) {
                return true;
            }

            return false;
        } else {
            if (!isset($value) || empty($value)) {
                return true;
            }

            return false;
        }
    }

    /**
     * @param array $params
     *
     * @return array|bool
     * @throws \Bitrix\Main\SystemException
     */
    public function getListCount($params = [])
    {
        $this->errors = [];

        if ($this->haveValue($params['HL_ID'], 'not') || intval($params['HL_ID']) <= 0) {
            if ($this->haveValue($this->HL_ID, 'not')) {
                $this->errors[] = static::$NOT_HL_ID;
            }
        } else {
            $this->HL_ID = $params['HL_ID'];
        }

        if (!empty($this->errors)) {
            return false;
        }

        $arUses = ['filter' => false];
        if ($this->haveValue($params['filter']) && is_array($params['filter'])) {
            $arUses['filter'] = true;
        }

        $this->initHLEntity($this->HL_ID, false, true);

        if ($arUses['filter']) {
            $this->main_query->setFilter($params['filter']);
        }

        $result = $this->main_query->exec();
        $count = $result->getSelectedRowsCount();

        return $count;
    }

    /**
     * @param array $params
     *
     * @return bool
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public function add($params = [])
    {
        $this->errors = [];

        if ($this->haveValue($params['HL_ID'], 'not') || intval($params['HL_ID']) <= 0) {
            if ($this->haveValue($this->HL_ID, 'not')) {
                $this->errors[] = static::$NOT_HL_ID;
            }
        } else {
            $this->HL_ID = $params['HL_ID'];
        }

        if ($this->haveValue($params['arData'], 'not')) {
            $this->errors[] = static::$EMPTY_DATA;
        }

        if (!empty($this->errors)) {
            return false;
        }

        $this->initHLEntity($this->HL_ID, true);

        $edc = $this->entity_data_class;
        //$result = $this->entity_data_class->add($params['arData']);
        $result = $edc::add($params['arData']);

        return $result->getId();
    }

    /**
     * @param array $params
     *
     * @return bool
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public function update($params = [])
    {
        $this->errors = [];

        if ($this->haveValue($params['HL_ID'], 'not') || intval($params['HL_ID']) <= 0) {
            if ($this->haveValue($this->HL_ID, 'not')) {
                $this->errors[] = static::$NOT_HL_ID;
            }
        } else {
            $this->HL_ID = $params['HL_ID'];
        }

        if ($this->haveValue($params['arData'], 'not')) {
            $this->errors[] = static::$EMPTY_DATA;
        }

        if ($this->haveValue($params['ID'], 'not')) {
            $this->errors[] = static::$EMPTY_ELEMENT_ID;
        }

        if (!empty($this->errors)) {
            return false;
        }

        $this->initHLEntity($this->HL_ID, true);

        $edc = $this->entity_data_class;
        //$result = $this->entity_data_class->update($params['ID'], $params['arData']);
        $result = $edc::update($params['ID'], $params['arData']);

        return $result->getId();
    }

    /**
     * @param array $params
     *
     * @return bool
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public function delete($params = [])
    {
        $this->errors = [];

        if ($this->haveValue($params['HL_ID'], 'not') || intval($params['HL_ID']) <= 0) {
            if ($this->haveValue($this->HL_ID, 'not')) {
                $this->errors[] = static::$NOT_HL_ID;
            }
        } else {
            $this->HL_ID = $params['HL_ID'];
        }

        if ($this->haveValue($params['ID'], 'not')) {
            $this->errors[] = static::$EMPTY_ELEMENT_ID;
        }

        if (!empty($this->errors)) {
            return false;
        }

        $this->initHLEntity($this->HL_ID, true);

        $edc = $this->entity_data_class;
        //$result = $this->entity_data_class->delete($params['ID']);
        $result = $edc::delete($params['ID']);

        return $result->isSuccess();
    }

    /**
     * @param array $select
     *
     * @return array|bool
     * @throws \Bitrix\Main\ArgumentException
     */
    public function getHLList($select = ['ID', 'NAME'])
    {
        $HLList = false;

        if (!is_array($select) || empty($select)) {
            $select = ['ID', 'NAME'];
        }

        $res = HighloadBlockTable::getList(['select' => $select]);
        while ($hlItem = $res->fetch()) {
            $HLList[] = $hlItem;
        }

        return $HLList;
    }

    /**
     * @param $params ['HL_ID']
     *
     * @return bool|array
     */
    public function getHLFields($params = [])
    {
        $this->errors = [];

        if ($this->haveValue($params['HL_ID'], 'not') || intval($params['HL_ID']) <= 0) {
            if ($this->haveValue($this->HL_ID, 'not')) {
                $this->errors[] = static::$NOT_HL_ID;
            }
        } else {
            $this->HL_ID = $params['HL_ID'];
        }

        if (!empty($this->errors)) {
            return false;
        }

        $arFields = [];

        $res = \CUserTypeEntity::GetList([], ['ENTITY_ID' => 'HLBLOCK_' . $this->HL_ID, 'LANG' => 'ru']);

        while ($arField = $res->Fetch()) {
            $arFields[] = [
                'CODE'    => $arField['FIELD_NAME'],
                'NAME'    => $arField['LIST_COLUMN_LABEL'],
                'FULL_EL' => $arField,
            ];
        }

        return $arFields;
    }

    /**
     * @param $HL_ID
     *
     * @throws \Bitrix\Main\SystemException
     */
    public function setHLID($HL_ID)
    {
        $this->HL_ID = $HL_ID;
        $this->initHLEntity($HL_ID);
    }

    /**
     * @param            $HL_ID
     * @param bool|false $bInitDataClass
     * @param bool|false $bInitMainQuery
     *
     * @return bool
     * @throws \Bitrix\Main\SystemException
     */
    protected function initHLEntity($HL_ID, $bInitDataClass = false, $bInitMainQuery = false)
    {
        $this->errors = [];

        if ($this->haveValue($HL_ID, 'not') || intval($HL_ID) <= 0) {
            $this->errors[] = static::$NOT_HL_ID;
        }

        if (!empty($this->errors)) {
            return false;
        }

        if ($this->haveValue($this->hlblock, 'not') || $this->hlblock['ID'] != $HL_ID) {
            $this->hlblock = HighloadBlockTable::getById($HL_ID)->fetch();
            $this->entity = HighloadBlockTable::compileEntity($this->hlblock);

            if ($bInitMainQuery) {
                $this->main_query = new Query($this->entity);
            }

            if ($bInitDataClass) {
                $this->entity_data_class = $this->entity->getDataClass();
            }
        } else {
            //if ($bInitMainQuery && $this->haveValue($this->main_query, 'not'))
            if ($bInitMainQuery) {
                $this->main_query = new Query($this->entity);
            }

            //if ($bInitDataClass && $this->haveValue($this->entity_data_class, 'not'))
            if ($bInitDataClass) {
                $this->entity_data_class = $this->entity->getDataClass();
            }
        }

        return true;
    }
}