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
    'align_multiline_comment' => true,
    'array_indentation' => true,
    'backtick_to_shell_exec' => true,
    'increment_style' => ['style' => 'post'],
    'indentation_type' => true,
    'multiline_comment_opening_closing' => true,
    'no_php4_constructor' => true,
    'phpdoc_no_empty_return' => false,
    'single_blank_line_at_eof' => false,
    'yoda_style' => false,
    'concat_space' => ['spacing' => 'one'],
    'no_superfluous_phpdoc_tags' => false,
];
return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules($rules)
    ->setIndent("\t")
    ->setLineEnding("\n")
    ->setFinder($finder);
