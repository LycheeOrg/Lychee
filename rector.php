<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Scripts\Rector\VariableCasingRector;

$rectorConfig = RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/bootstrap',
        __DIR__ . '/config',
        __DIR__ . '/lang',
        __DIR__ . '/public',
        __DIR__ . '/resources',
        __DIR__ . '/routes',
        __DIR__ . '/scripts',
        __DIR__ . '/tests',
    ])
    // uncomment to reach your current PHP version
    // ->withPhpSets()
    ->withTypeCoverageLevel(0)
    ->withDeadCodeLevel(0)
    ->withCodeQualityLevel(0);
$rectorConfig->withRules([
	VariableCasingRector::class,
])->withSkip([
    VariableCasingRector::class => [
        __DIR__ . '/app/Assets/ArrayToTextTable.php',
    ],
    __DIR__ . '/app/Metadata/Laminas/Unicode.php',
]);
return $rectorConfig;