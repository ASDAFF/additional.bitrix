<?php

namespace InetSys\Debug;

/**
 * Class Logger
 * @package InetSys\Debug
 */
class Logger
{
    protected static $instance = [];

    protected $fileLog;
    protected $pathToLog = '/local/logs/';
    protected $fp;
    protected $active = false;

    /**
     * Logger constructor.
     *
     * @param string $name
     */
    public function __construct($name = 'main')
    {
        $this->activate(true);
        $this->setType($name);
    }

    /**
     * @param string $name
     *
     * @return static
     */
    public static function getInstance($name = 'main')
    {
        if (!isset(self::$instance[$name])) {
            $c = __CLASS__;
            self::$instance[$name] = new $c($name);
        }

        return self::$instance[$name];
    }

    /**
     * @param bool $flag
     */
    public function activate($flag = true)
    {
        $this->active = $flag;
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function setType($name)
    {
        if (!$this->active) {
            return false;
        }

        if ($name !== false) {
            $this->fileLog = $_SERVER['DOCUMENT_ROOT'] . $this->pathToLog . $name . '.log';
        } else {
            if (!isset($this->fileLog) || empty($this->fileLog)) {
                $name = 'default';
                $this->fileLog = $_SERVER['DOCUMENT_ROOT'] . $this->pathToLog . $name . '.log';
            }
        }

        return true;
    }

    /**
     * @param      $data
     * @param bool $type
     *
     * @return bool
     */
    public function write($data, $type = false)
    {
        if (empty($data) || (!$data) || (!$this->active)) {
            return false;
        }

        $this->setType($type);
        $this->openFile();

        $formatedData = date('d.m.Y H:i:s - ');
        if (is_array($data)) {
            $formatedData .= print_r($data, true);
        } else {
            $formatedData .= $data;
        }
        $formatedData .= "\r\n";
        fwrite($this->fp, $formatedData);

        $this->closeFile();

        return true;
    }

    /**
     * @param string $endLine
     */
    public function writeEndLine($endLine = '------------------')
    {
        $this->write($endLine);
    }

    /**
     * @param string $endLine
     */
    public function writeSeparator($endLine = '---')
    {
        $this->write($endLine);
    }

    /**
     *
     */
    protected function openFile()
    {
        if (file_exists($this->fileLog)) {
            $mode = 'a';
        } else {
            $mode = 'w';
        }
        $mode .= 't';
        $this->fp = fopen($this->fileLog, $mode);
    }

    /**
     *
     */
    protected function closeFile()
    {
        fclose($this->fp);
    }
}