<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/sample',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->exclude([
        'runtime',
        'dev/giiant/generators/model/templates',
        '_runtime',
        '_support/_generated',
    ])
    ->notPath(
        // Exclude till https://github.com/FriendsOfPHP/PHP-CS-Fixer/pull/6408 is merged.
       'unit/components/repositories/base/Oauth2BaseTokenRepositoryTest.php'
    );

$config = new PhpCsFixer\Config();

return $config
    ->setFinder($finder)
    ->setRules([
        '@PSR12' => true,
        'function_declaration' => false,
        'new_with_braces' => [
            'anonymous_class' => false,
        ],
    ]);
