<?php

namespace InetSys\Decorators;

use Bitrix\Main\Application;
use Bitrix\Main\SystemException;
use InetSys\Debug\Logger;

/**
 * Project specific SvgDecorator
 *
 * @package InetSys\Decorators
 */
class FullHrefDecorator
{
    /** @var string Домен */
    private static $host = null;
    /** @var string Протокол: http|https */
    private static $proto = null;
    private $path;

    /**
     * FullHrefDecorator constructor.
     *
     * @param string $path
     */
    public function __construct($path)
    {
        $this->setPath($path);
    }

    /**
     * @param $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->getFullPublicPath();
        } catch (SystemException $e) {
            try {
                $logger = Logger::getInstance('fullHrefDecorator');
                $logger->write('Системная ошибка при получении пукбличного пути ' . $e->getTraceAsString());
            } catch (\RuntimeException $e) {
            }

            return '';
        }
    }

    /**
     * @throws SystemException
     * @return string
     */
    public function getFullPublicPath()
    {
        $prefix = $this->getProto();
        $host = $this->getHost();

        return $prefix . '://' . $host . $this->path;
    }

    /**
     * @return string
     */
    public function getStartPath()
    {
        return $this->path;
    }

    /**
     * @return string
     * @throws SystemException
     */
    public function getProto()
    {
        if (static::$proto === null) {
            static::$proto = 'http';
            $context = Application::getInstance()->getContext();
            if ($context->getRequest()->isHttps()) {
                static::$proto .= 's';
            }
        }

        return static::$proto;
    }

    /**
     * Сброс значения протокола
     */
    public function flushProto()
    {
        static::$proto = null;
    }

    /**
     * @param string $host
     *
     * @return FullHrefDecorator
     */
    public function setHost($host)
    {
        $this::$host = $host;

        return $this;
    }

    /**
     * @return string
     * @throws SystemException
     */
    public function getHost()
    {
        if (static::$host === null) {
            $context = Application::getInstance()->getContext();
            static::$host = $context->getServer()->getHttpHost();
            static::$host = static::$host ? trim(static::$host) : '';

            // в cli нет HTTP_HOST, пробуем через константу
            if (static::$host === '' && defined('SITE_SERVER_NAME')) {
                static::$host = trim(SITE_SERVER_NAME);
            }
            // ... или через сайт
            if (static::$host === '') {
                $site = \CSite::GetList(
                    $by = 'SORT',
                    $order = 'ASC',
                    [
                        'ACTIVE' => 'Y',
                        //'DEFAULT' => 'Y',
                    ]
                )->Fetch();
                if ($site) {
                    static::$host = $site['SERVER_NAME'];
                }
            }
        }

        return static::$host;
    }

    /**
     * Сброс значения хоста
     */
    public function flushHost()
    {
        static::$host = null;
    }
}
