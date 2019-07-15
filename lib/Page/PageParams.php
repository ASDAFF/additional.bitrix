<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 07.09.18
 * Time: 13:42
 */

namespace InetSys\Page;


class PageParams
{
    protected static $instance;
    protected $arCurLanguage = [];
    protected $arCurPage = [];

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
     *
     */
    public function setPageLangValues()
    {
        global $APPLICATION;

        if ($this->arCurLanguage['IS_NONE_RU']) {
            $arLanguageProps = [
                'description' . $this->arCurLanguage['POSTFIX_ORIGINAL']    => '',
                'keywords' . $this->arCurLanguage['POSTFIX_ORIGINAL']       => '',
                'title' . $this->arCurLanguage['POSTFIX_ORIGINAL']          => '',
                'keywords_inner' . $this->arCurLanguage['POSTFIX_ORIGINAL'] => '',
                'main_title' . $this->arCurLanguage['POSTFIX_ORIGINAL']     => '',
            ];

            foreach ($arLanguageProps as $prop_id => $val) {
                $val = $APPLICATION->GetDirProperty($prop_id);
                $base_prop_id = str_replace($this->arCurLanguage['POSTFIX_ORIGINAL'], '', $prop_id);
                if (!empty($val)) {
                    if ($base_prop_id == 'main_title') {
                        $APPLICATION->SetTitle($val);
                    } else {
                        $APPLICATION->SetDirProperty($base_prop_id, $val);
                    }
                    $arLanguageProps[$prop_id] = $val;
                }

                $val = $APPLICATION->GetPageProperty($prop_id);
                if (!empty($val)) {
                    if ($base_prop_id == 'main_title') {
                        $APPLICATION->SetTitle($val);
                    } else {
                        $APPLICATION->SetPageProperty($base_prop_id, $val);
                    }
                    $arLanguageProps[$prop_id] = $val;
                }
            }
        }
    }

    /**
     * @param bool $arAdditional
     */
    public function setCurrentPage($arAdditional = false)
    {
        global $APPLICATION;

        $this->arCurPage['DIR'] = $APPLICATION->GetCurDir();
        $this->arCurPage['PAGE'] = $APPLICATION->GetCurPage();
        $this->arCurPage['PAGE_INDEX'] = $APPLICATION->GetCurPage(true);
        $this->arCurPage['IS_INDEX'] = false;
        if ($this->arCurPage['PAGE'] == SITE_DIR && $this->arCurPage['PAGE_INDEX'] == SITE_DIR . 'index.php') {
            $this->arCurPage['IS_INDEX'] = true;
        }

        if ($arAdditional != false && is_array($arAdditional) && !empty($arAdditional)) {
            foreach ($arAdditional as $arPath) {
                $this->arCurPage[$arPath['NAME']] = false;
                $this->arCurPage[$arPath['NAME'] . '_INDEX'] = false;
                if (false !== strpos($this->arCurPage['PAGE'], $arPath['PATH'])) {
                    $this->arCurPage[$arPath['NAME']] = true;
                    if (false !== strpos($this->arCurPage['PAGE_INDEX'], $arPath['PATH'] . 'index.php')) {
                        $this->arCurPage[$arPath['NAME'] . '_INDEX'] = true;
                    }
                }
            }
        }
    }

    /**
     *
     */
    public function setLanguageVars()
    {
        $this->arCurLanguage['IS_NONE_RU'] = false;
        $this->arCurLanguage['POSTFIX'] = '';
        if (LANGUAGE_ID != 'ru') {
            $this->arCurLanguage['IS_NONE_RU'] = true;
            $this->arCurLanguage['POSTFIX'] = '_' . ToUpper(LANGUAGE_ID);
            $this->arCurLanguage['POSTFIX_ORIGINAL'] = '_' . LANGUAGE_ID;
            $this->arCurLanguage['POSTFIX_MINI'] = '_' . ToLower(LANGUAGE_ID);
        }
    }

    /**
     * @return array
     */
    public function getCurLanguage()
    {
        return $this->arCurLanguage;
    }

    /**
     * @return array
     */
    public function getCurPage()
    {
        return $this->arCurPage;
    }
}