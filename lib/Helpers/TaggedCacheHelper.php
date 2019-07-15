<?php

namespace InetSys\Helpers;

use Bitrix\Main\Application;
use Bitrix\Main\Data\TaggedCache;
use Bitrix\Main\SystemException;
use InetSys\Debug\Logger;

/**
 * Class TaggedCacheHelper
 *
 * @package InetSys\Helpers
 */
class TaggedCacheHelper
{
    /** @var TaggedCache */
    protected $tagCacheInstance;

    public function __construct($cachePath = '')
    {
        $this->start($cachePath);
    }

    /**
     * Получение объекта тегированного кеша
     * @return TaggedCache|null
     */
    public static function getTagCacheInstance()
    {
        $tagCache = null;
        if (\defined('BX_COMP_MANAGED_CACHE')) {
            try {
                $tagCache = Application::getInstance()->getTaggedCache();

            } catch (SystemException $e) {
                /** скипаем и возвращаем null */
            }
        }

        return $tagCache;
    }

    /**
     * Добавление тегов массивом
     *
     * @param array            $tags
     * @param TaggedCache|null $tagCache
     */
    public static function addManagedCacheTags(array $tags, TaggedCache $tagCache = null)
    {
        if (!\defined('BX_COMP_MANAGED_CACHE')) {
            return;
        }

        if ($tagCache === null) {
            $tagCache = static::getTagCacheInstance();
        }

        foreach ($tags as $tag) {
            static::addManagedCacheTag($tag, $tagCache);
        }
    }

    /**
     * Очистка кеша по тегам
     *
     * @param array $tags
     */
    public static function clearManagedCache(array $tags)
    {
        if (!\defined('BX_COMP_MANAGED_CACHE')) {
            return;
        }

        $tagCache = static::getTagCacheInstance();
        if ($tagCache === null) {
            return;
        }

        foreach ($tags as $tag) {
            try {
                $tagCache->clearByTag($tag);
            } catch (\Exception $e) {
                $logger = Logger::getInstance('tagCache');
                $logger->write(sprintf('%s: %s', \get_class($e), $e->getMessage()));
            }
        }
    }

    /**
     * Добавление одного тега
     *
     * @param string           $tag
     * @param TaggedCache|null $tagCache
     */
    public static function addManagedCacheTag($tag, TaggedCache $tagCache = null)
    {
        if (!\defined('BX_COMP_MANAGED_CACHE')) {
            return;
        }

        if ($tagCache === null) {
            $tagCache = static::getTagCacheInstance();
        }

        $tagCache->registerTag($tag);
    }

    /**
     * Начинаем тегирвоанный кеш
     *
     * @param string $cachePath
     *
     * @return TaggedCacheHelper
     */
    public function start($cachePath = '')
    {
        $this->tagCacheInstance = static::getTagCacheInstance();
        if ($this->tagCacheInstance !== null) {
            $this->tagCacheInstance->startTagCache($cachePath);
        }
        return $this;
    }

    /** Завершаем тегирвоанный кеш */
    public function end()
    {
        if ($this->tagCacheInstance !== null) {
            $this->tagCacheInstance->endTagCache();
        }
    }

    /**
     * Добавляем теги
     *
     * @param array $tags
     *
     * @return TaggedCacheHelper
     */
    public function addTags(array $tags)
    {
        if ($this->tagCacheInstance === null) {
            $this->start();
        }
        static::addManagedCacheTags($tags, $this->tagCacheInstance);
        return $this;
    }

    /**
     * Добавляем тег
     *
     * @param string $tag
     *
     * @return TaggedCacheHelper
     */
    public function addTag($tag)
    {
        if ($this->tagCacheInstance === null) {
            $this->start();
        }
        static::addManagedCacheTag($tag, $this->tagCacheInstance);
        return $this;
    }

    /** прерываем тегированный кеш(abort) */
    public function abortTagCache()
    {
        $this->tagCacheInstance->abortTagCache();
    }
}
