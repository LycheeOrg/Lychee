<?php
$finder = array_reduce(
    [
        __DIR__ . '/app/',
        __DIR__ . '/database/',
        __DIR__ . '/resources/',
        __DIR__ . '/routes/',
        __DIR__ . '/tests/',
    ],
    function (PhpCsFixer\Finder $finder, $dir) {
        return $finder->in($dir);
    },
    PhpCsFixer\Finder::create()->ignoreUnreadableDirs()
)->notName('*.blade.php');
$rules = [
    '@Symfony' => true,
    'array_indentation' => true,
    'align_multiline_comment' => true,
    'backtick_to_shell_exec' => true,
    'indentation_type' => true,
    'no_php4_constructor' => true,
    'phpdoc_no_empty_return' => false,
    'yoda_style' => false,
];
return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules($rules)
    ->setIndent("\t")
    ->setLineEnding("\n")
    ->setFinder($finder);