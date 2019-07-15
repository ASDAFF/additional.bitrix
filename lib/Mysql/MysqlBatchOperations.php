<?php

namespace InetSys\Mysql;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Db\SqlQueryException;
use Bitrix\Main\Entity\Query;

class MysqlBatchOperations
{
    /** @var ExtendsBitrixQuery */
    private $query;
    private $lowPriority = false;
    private $quick = false;
    private $delayed = false;
    private $ignore = false;
    private $table = '';
    private $limit = 500;
    private $step = 0;

    /**
     * Устанавливаем сформирвоанный объект с запросом
     * MysqlBatchOperations constructor.
     *
     * @param Query  $query
     * @param string $table
     *
     * @throws ArgumentException
     */
    public function __construct(Query $query = null, $table = '')
    {
        if ($query instanceof Query) {
            $this->setQuery($query);
        }
        if (!empty($table)) {
            $this->setTable($table);
        }
    }

    /**
     * Делаем массовое обновление данных по условию
     *
     * @param array $fields
     *
     * @throws SqlQueryException
     */
    public function batchUpdate(array $fields)
    {
        /** @todo обновление из нескольких таблиц */
        /** @todo транзакции */
        if (!empty($fields) && $this->hasTable()) {
            $updates = [];
            foreach ($fields as $column => $val) {
                $updates[] = $column . '=' . $val;
            }
            $connection = Application::getConnection();
            $queryString = 'UPDATE' . $this->getLowPriority() . $this->getIgnore() . ' ' . $this->getTable()
                . ' SET ' . implode(', ', $updates)
                . $this->getWhere() . $this->getOrder() . $this->getLimitString();
            $connection->queryExecute($queryString);
        }
    }

    /**
     * Делаем массовое удаление по условию
     * @throws SqlQueryException
     */
    public function batchDelete()
    {
        /** @todo удаление из нескольких таблиц */
        /** @todo использование USING */
        if ($this->hasTable()) {
            $connection = Application::getConnection();
            $queryString = 'DELETE' . $this->getLowPriority() . $this->getQuick() . ' FROM ' . $this->getTable()
                . $this->getWhere() . $this->getOrder() . $this->getLimitString();
            $connection->queryExecute($queryString);
        }
    }

    /**
     * Делаем массовую вставку
     *
     * @param array $fields
     *
     * @throws SqlQueryException
     */
    public function batchInsert(array $fields)
    {
        /** @todo транзакции */
        /** @todo вставка по подзапросу в том числе с лимитом */
        /** @todo использование ON DUPLICATE KEY UPDATE */
        if ($this->hasTable()) {
            $fields = $this->getPart($fields);
            if (!empty($fields)) {
                $values = [];
                $columns = [];
                foreach ($fields as $item) {
                    $values[] = '(' . implode(', ', array_values($item)) . ')';
                    /** @noinspection SlowArrayOperationsInLoopInspection */
                    $columns = array_merge($columns, array_keys($item));
                }
                array_unique($columns);
                $connection = Application::getConnection();
                $queryString = 'INSERT' . $this->getDelayed() . ($this->isDelayed() ? '' : $this->getLowPriority()) . $this->getIgnore()
                    . ' INTO ' . $this->getTable() . '(' . implode(', ', $columns) . ')'
                    . ' VALUES ' . implode(', ', $values);
                $connection->queryExecute($queryString);
            }
        }
    }

    /**
     * Получение части массива по лимтам
     *
     * @param $items
     *
     * @return array
     */
    public function getPart($items)
    {
        $limit = $this->getLimit();
        if ($limit > 0 && \count($items) > $limit) {
            $chunkItems = array_chunk($items, $limit);
            $countChunkItems = \count($chunkItems);
            $step = $this->getStep();
            if ($countChunkItems > $step) {
                $items = $chunkItems[$step];
                $this->increaseStep();
            } else {
                $items = [];
                $this->clearStep();
            }
        }
        return $items;
    }

    /**
     * Получаем ограничение в limit
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Устанавливаем ограничение в limit
     *
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * Проверяем LOW_PRIORITY
     * @return bool
     */
    public function isLowPriority()
    {
        return $this->lowPriority;
    }

    /**
     * Устанавливаем LOW_PRIORITY
     *
     * @param bool $lowPriority
     */
    public function setLowPriority($lowPriority)
    {
        $this->lowPriority = $lowPriority;
    }

    /**
     * Првоеряем QUICK
     * @return bool
     */
    public function isQuick()
    {
        return $this->quick;
    }

    /**
     * Устанавливаем QUICK
     *
     * @param bool $quick
     */
    public function setQuick($quick)
    {
        $this->quick = $quick;
    }

    /**
     * Получаем имя таблицы
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Устанавливаем имя таблицы
     *
     * @param string $table
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    /**
     * Проверка установки DELAYED
     * @return bool
     */
    public function isDelayed()
    {
        return $this->delayed;
    }

    /**
     * Установка DELAYED
     *
     * @param bool $delayed
     */
    public function setDelayed($delayed)
    {
        $this->delayed = $delayed;
    }

    /**
     * Проверка установки IGNORE
     * @return bool
     */
    public function isIgnore()
    {
        return $this->ignore;
    }

    /**
     * Получение строки IGNORE
     * @return string
     */
    public function getIgnore()
    {
        return $this->isIgnore() ? ' IGNORE' : '';
    }

    /**
     * Установка IGNORE
     *
     * @param bool $ignore
     */
    public function setIgnore($ignore)
    {
        $this->ignore = $ignore;
    }

    /**
     * Получение установленного объекта Query
     * @return ExtendsBitrixQuery
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Установка объекта Query
     *
     * @param Query $query
     *
     * @throws \Bitrix\Main\ArgumentException
     */
    public function setQuery(Query $query)
    {
        $this->query = new ExtendsBitrixQuery($query);
        $this->setTable($this->query->quoteTableSource($this->query->getEntity()->getDBTableName()));
    }

    /**
     * Получение строки LOW_PRIORITY
     * @return string
     */
    private function getLowPriority()
    {
        return $this->isLowPriority() ? ' LOW_PRIORITY' : '';
    }

    /**
     * получение строки QUICK
     * @return string
     */
    private function getQuick()
    {
        return $this->isQuick() ? ' QUICK' : '';
    }

    /**
     * Првоерка существования таблицы
     * @return bool
     */
    private function hasTable()
    {
        return !empty($this->getTable());
    }

    /**
     * Получение строки DELAYED
     * @return string
     */
    private function getDelayed()
    {
        return $this->isDelayed() ? ' DELAYED' : '';
    }

    /**
     * Получение шага
     * @return int
     */
    private function getStep()
    {
        return $this->step;
    }

    /**
     * Установка шага
     *
     * @param int $step
     */
    private function setStep($step)
    {
        $this->step = $step;
    }

    /** Наращивание шага */
    private function increaseStep()
    {
        $this->setStep($this->getStep() + 1);
    }

    /** Очистка шага */
    private function clearStep()
    {
        $this->setStep(0);
    }

    /**
     * Получение строки LIMIT
     * @return string
     */
    private function getLimitString()
    {
        if ($this->query instanceof ExtendsBitrixQuery) {
            return $this->query->getLimit() > 0 ? ' LIMIT ' . $this->query->getLimit() : '';
        }

        return '';
    }

    /**
     * Получение строки WHERE
     * @return string
     */
    private function getWhere()
    {
        return $this->query->getBuildWhere();
    }

    /**
     * Получение стркои ORDER
     * @return string
     */
    private function getOrder()
    {
        return $this->query->getBuildOrder();
    }
}
