<?php
/** либо выбираем текущий конфиг, либо настраиваем свой */
//$config = new DotsUnited\PhpCsFixer\Php56Config();
//$config = new DotsUnited\PhpCsFixer\Php71Config();
$config = \PhpCsFixer\Config::create()
    ->setUsingCache(true)
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR2'                                     => true,
        'linebreak_after_opening_tag'               => true,
        'no_multiline_whitespace_before_semicolons' => true,
        'no_php4_constructor'                       => true,
        'no_useless_else'                           => true,
        'ordered_imports'                           => true,
        'php_unit_construct'                        => true,
        'phpdoc_order'                              => true,
        'pow_to_exponentiation'                     => true,
        'random_api_migration'                      => true,
        'align_multiline_comment'                   => true,
        'phpdoc_types_order'                        => true,
        'no_null_property_initialization'           => true,
        'no_unneeded_final_method'                  => true,
        'no_unneeded_curly_braces'                  => true,
        'no_superfluous_elseif'                     => true,
        'trailing_comma_in_multiline_array'         => true,
        'no_unused_imports'                         => true,
        'include'                                   => true,
        'array_syntax'                              => [
            'syntax' => 'short',
        ],
    ]);

$cacheDir = getenv('TRAVIS') ? getenv('HOME') . '/.php-cs-fixer' : __DIR__;
$config->setCacheFile($cacheDir . '/.php_cs.cache');

// устанавливаем где искать
$config->getFinder()
    ->in([__DIR__])
    ->exclude([
        __DIR__ . '/bitrix',
        __DIR__ . '/upload',
        __DIR__ . '/local/modules',
        __DIR__ . '/local/docs',
    ])
    ->files()
    ->name('*.php');

return $config;
