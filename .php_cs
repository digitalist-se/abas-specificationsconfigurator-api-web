<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->exclude('bootstrap')
    ->exclude('storage')
    ->exclude('vendor')
    ->in(getcwd())
    ->name('*.php')
    ->name('*.phpt')
    ->notName('*.blade.php')
    ->notName('_ide_helper.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

// @see https://mlocati.github.io/php-cs-fixer-configurator/#version:2.18.2
$rules = [
    '@Symfony'               => true,
    'binary_operator_spaces' => [
        'align_double_arrow' => true,
        'align_equals'       => true,
    ],
    'braces' => [
        'allow_single_line_closure' => true,
    ],
    'ordered_imports'                  => true,
    'no_empty_comment'                 => false,
    'no_extra_consecutive_blank_lines' => [
        'tokens' => [
            'curly_brace_block',
            'parenthesis_brace_block',
            'extra',
            'throw',
            'use',
        ],
    ],
    'no_useless_else'         => true,
    'no_useless_return'       => true,
    'new_with_braces'         => false,
    'phpdoc_var_without_name' => false,
    'php_unit_method_casing'  => [
        'case' => 'snake_case',
    ],
    'php_unit_test_case_static_method_calls' => [
        'call_type' => 'static',
    ],
];

return Config::create()
    ->setFinder($finder)
    ->setRules($rules)
    ->setRiskyAllowed(true)
    ->setUsingCache(true);
