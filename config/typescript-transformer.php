<?php

use Spatie\LaravelTypeScriptTransformer\Transformers\DtoTransformer;
use Spatie\LaravelTypeScriptTransformer\Transformers\SpatieStateTransformer;
use Spatie\TypeScriptTransformer\Collectors\DefaultCollector;
use Spatie\TypeScriptTransformer\Collectors\EnumCollector;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;
use Spatie\TypeScriptTransformer\Transformers\SpatieEnumTransformer;
use Spatie\TypeScriptTransformer\Writers\TypeDefinitionWriter;

return [
	/*
	 * The paths where typescript-transformer will look for PHP classes
	 * to transform, this will be the `app` path by default.
	 */

	'auto_discover_types' => [
		app_path(),
	],

	/*
	 * Collectors will search for classes in the `auto_discover_types` paths and choose the correct
	 * transformer to transform them. By default, we include a DefaultCollector which will search
	 * for @typescript annotated and #[TypeScript] attributed classes to transform.
	 */

	'collectors' => [
		DefaultCollector::class,
		EnumCollector::class,
	],

	/*
	 * Transformers take PHP classes(e.g., enums) as an input and will output
	 * a TypeScript representation of the PHP class.
	 */

	'transformers' => [
		SpatieStateTransformer::class,
		EnumTransformer::class,
		SpatieEnumTransformer::class,
		DtoTransformer::class,
	],

	/*
	 * In your classes, you sometimes have types that should always be replaced
	 * by the same TypeScript representations. For example, you can replace a
	 * Datetime always with a string. You define these replacements here.
	 */

	'default_type_replacements' => [
		DateTime::class => 'string',
		DateTimeImmutable::class => 'string',
		Carbon\CarbonInterface::class => 'string',
		Carbon\CarbonImmutable::class => 'string',
		Carbon\Carbon::class => 'string',
	],

	/*
	 * The package will write the generated TypeScript to this file.
	 */

	'output_file' => resource_path('js/lychee.d.ts'),

	/*
	 * When the package is writing types to the output file, a writer is used to
	 * determine the format. By default, this is the `TypeDefinitionWriter`.
	 * But you can also use the `ModuleWriter` or implement your own.
	 */

	'writer' => TypeDefinitionWriter::class,

	/*
	 * The generated TypeScript file can be formatted. We ship a Prettier formatter
	 * out of the box: `PrettierFormatter` but you can also implement your own one.
	 * The generated TypeScript will not be formatted when no formatter was set.
	 */

	'formatter' => null,

	/*
	 * Enums can be transformed into types or native TypeScript enums, by default
	 * the package will transform them to types.
	 */

	'transform_to_native_enums' => false,
];
