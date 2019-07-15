<?php

namespace InetSys\Helpers;


/**
 * Class ClassFinderHelper
 * @package InetSys\Helpers
 */
class ClassFinderHelper
{
    /**
     * Поиск классов с совпадением имени в определенной папке
     *
     * @param string $findNamespace
     * @param string $findDir
     *
     * @return array
     */
    public static function getClasses($findNamespace, $findDir)
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($findDir));
        $regex = new \RegexIterator($iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);
        $classes = [];
        foreach ($regex as $file => $value) {
            $current = static::parseTokens(token_get_all(file_get_contents(str_replace('\\', '/', $file))));
            if ($current !== false) {
                list($namespace, $class) = $current;
                if ($namespace === $findNamespace) {
                    $classes[] = $namespace . $class;
                }
            }
        }
        return $classes;
    }

    private static function parseTokens(array $tokens)
    {
        $nsStart = false;
        $classStart = false;
        $namespace = '';
        foreach ($tokens as $token) {
            if ($token[0] === T_CLASS) {
                $classStart = true;
            }
            if ($classStart && $token[0] === T_STRING) {
                return [$namespace, $token[1]];
            }
            if ($token[0] === T_NAMESPACE) {
                $nsStart = true;
            }
            if ($nsStart && $token[0] === ';') {
                $nsStart = false;
            }
            if ($nsStart && $token[0] === T_STRING) {
                $namespace .= $token[1] . '\\';
            }
        }

        return false;
    }
}