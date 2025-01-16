<?php

$finder = array_reduce(
	[
		__DIR__ . '/app/',
		__DIR__ . '/database/',
		__DIR__ . '/lang/',
		__DIR__ . '/config/',
		__DIR__ . '/resources/',
		__DIR__ . '/routes/',
		__DIR__ . '/tests/',
		__DIR__ . '/scripts/',
	],
	function (PhpCsFixer\Finder $finder, $dir) {
		return $finder->in($dir);
	},
	PhpCsFixer\Finder::create()->ignoreUnreadableDirs()
)->notName('*.blade.php');
$rules = [
	'@Symfony' => true,
	'nullable_type_declaration_for_default_null_value' => true,
	'align_multiline_comment' => true,
	'array_indentation' => true,
	'fully_qualified_strict_types' => false,
	'backtick_to_shell_exec' => true,
	'new_with_parentheses' => true,
	'increment_style' => ['style' => 'post'],
	'indentation_type' => true,
	'multiline_comment_opening_closing' => true,
	'no_php4_constructor' => true,
	'nullable_type_declaration' => false,
	'phpdoc_no_empty_return' => false,
	'single_blank_line_at_eof' => false,
	'yoda_style' => false,
	'concat_space' => ['spacing' => 'one'],
	'no_superfluous_phpdoc_tags' => false,
	'phpdoc_to_comment' => false, // required until https://github.com/phpstan/phpstan/issues/7486 got fixed
	'blank_line_between_import_groups' => false, // not PSR-12 compatible, but preserves old behaviour
	'ordered_imports' => [
		'sort_algorithm' => 'alpha',
		'imports_order' => null, // for PSR-12 compatability, this need to be `['class', 'function', 'const']`, but no grouping preserves old behaviour
	],
	'no_unneeded_control_parentheses' => [
		'statements' => ['break', 'clone', 'continue', 'echo_print', 'switch_case', 'yield'],
	],
	'operator_linebreak' => [
		'only_booleans' => true,
		'position' => 'end',
	],
	// 'header_comment' => ['header' => "SPDX-License-Identifier: MIT\nCopyright (c) 2017-2018 Tobias Reich\nCopyright (c) 2018-2025 LycheeOrg", 'comment_type' => 'PHPDoc', 'location' => 'after_open', 'separate' => 'bottom'],
];
$config = new PhpCsFixer\Config();

$config->setRiskyAllowed(true);
$config->setRules($rules);
$config->setIndent("\t");
$config->setLineEnding("\n");
$config->setFinder($finder);

return $config;
