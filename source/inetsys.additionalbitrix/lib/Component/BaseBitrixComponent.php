<?php

namespace InetSys\Component;


use InetSys\Debug\Logger;
use RuntimeException;

/**
 * Class BaseBitrixComponent
 *
 * Default component for current project
 *
 * @package InetSys
 */
abstract class BaseBitrixComponent extends \CBitrixComponent
{
    /**
     * @var string
     */
    private $templatePage = '';
    /** @var Logger */
    private $log;

    /**
     * @param $params
     *
     * @return array
     */
    public function onPrepareComponentParams($params)
    {
        $logName = \sprintf(
            'component_%s',
            static::class
        );
        $this->log = Logger::getInstance($logName);

        $params['return_result'] = $params['return_result'] === true || $params['return_result'] === 'Y';

        return parent::onPrepareComponentParams($params);
    }

    /**
     * {@inheritdoc}
     *
     * @throws RuntimeException
     * @return null|array
     */
    public function executeComponent()
    {
        if ($this->startResultCache()) {

            try {
                parent::executeComponent();

                $this->prepareResult();

                $this->includeComponentTemplate($this->templatePage);
            } catch (\Exception $e) {
                $this->log->write(sprintf('%s: %s', \get_class($e), $e->getMessage()), [
                    'trace' => $e->getTrace(),
                ]);
                $this->abortResultCache();
            }

            $this->setResultCacheKeys($this->getResultCacheKeys());
        }

        if ($this->arParams['return_result']) {
            return $this->arResult;
        }

        return null;
    }

    /**
     * Prepare component result
     */
    abstract public function prepareResult();

    /**
     * @return array
     */
    public function getResultCacheKeys()
    {
        return [];
    }

    /**
     * @param string $page
     */
    protected function setTemplatePage($page)
    {
        $this->templatePage = $page;
    }
}
