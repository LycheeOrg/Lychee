<?php

use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Normalizers\ArrayableNormalizer;
use Spatie\LaravelData\Normalizers\ArrayNormalizer;
use Spatie\LaravelData\Normalizers\JsonNormalizer;
use Spatie\LaravelData\Normalizers\ModelNormalizer;
use Spatie\LaravelData\Normalizers\ObjectNormalizer;
use Spatie\LaravelData\RuleInferrers\AttributesRuleInferrer;
use Spatie\LaravelData\RuleInferrers\BuiltInTypesRuleInferrer;
use Spatie\LaravelData\RuleInferrers\NullableRuleInferrer;
use Spatie\LaravelData\RuleInferrers\RequiredRuleInferrer;
use Spatie\LaravelData\RuleInferrers\SometimesRuleInferrer;
use Spatie\LaravelData\Transformers\ArrayableTransformer;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;
use Spatie\LaravelData\Transformers\EnumTransformer;

return [
	/**
	 * The package will use this format when working with dates. If this option
	 * is an array, it will try to convert from the first format that works,
	 * and will serialize dates using the first format from the array.
	 */
	'date_format' => DATE_ATOM,

	/**
	 * When transforming or casting dates, the following timezone will be used to
	 * convert the date to the correct timezone. If set to null no timezone will
	 * be passed.
	 */
	'date_timezone' => null,

	/**
	 * It is possible to enable certain features of the package, these would otherwise
	 * be breaking changes, and thus they are disabled by default. In the next major
	 * version of the package, these features will be enabled by default.
	 */
	'features' => [
		'cast_and_transform_iterables' => false,

		/**
		 * When trying to set a computed property value, the package will throw an exception.
		 * You can disable this behaviour by setting this option to true, which will then just
		 * ignore the value being passed into the computed property and recalculate it.
		 */
		'ignore_exception_when_trying_to_set_computed_property_value' => false,
	],

	/**
	 * Global transformers will take complex types and transform them into simple
	 * types.
	 */
	'transformers' => [
		DateTimeInterface::class => DateTimeInterfaceTransformer::class,
		\Illuminate\Contracts\Support\Arrayable::class => ArrayableTransformer::class,
		BackedEnum::class => EnumTransformer::class,
	],

	/**
	 * Global casts will cast values into complex types when creating a data
	 * object from simple types.
	 */
	'casts' => [
		DateTimeInterface::class => DateTimeInterfaceCast::class,
		BackedEnum::class => EnumCast::class,
		//        Enumerable::class => Spatie\LaravelData\Casts\EnumerableCast::class,
	],

	/**
	 * Rule inferrers can be configured here. They will automatically add
	 * validation rules to properties of a data object based upon
	 * the type of the property.
	 */
	'rule_inferrers' => [
		SometimesRuleInferrer::class,
		NullableRuleInferrer::class,
		RequiredRuleInferrer::class,
		BuiltInTypesRuleInferrer::class,
		AttributesRuleInferrer::class,
	],

	/**
	 * Normalizers return an array representation of the payload, or null if
	 * it cannot normalize the payload. The normalizers below are used for
	 * every data object, unless overridden in a specific data object class.
	 */
	'normalizers' => [
		ModelNormalizer::class,
		// FormRequestNormalizer::class,
		ArrayableNormalizer::class,
		ObjectNormalizer::class,
		ArrayNormalizer::class,
		JsonNormalizer::class,
	],

	/**
	 * Data objects can be wrapped into a key like 'data' when used as a resource,
	 * this key can be set globally here for all data objects. You can pass in
	 * `null` if you want to disable wrapping.
	 */
	'wrap' => null,

	/**
	 * Adds a specific caster to the Symphony VarDumper component which hides
	 * some properties from data objects and collections when being dumped
	 * by `dump` or `dd`. Can be 'enabled', 'disabled' or 'development'
	 * which will only enable the caster locally.
	 */
	'var_dumper_caster_mode' => 'development',

	/**
	 * It is possible to skip the PHP reflection analysis of data objects
	 * when running in production. This will speed up the package. You
	 * can configure where data objects are stored and which cache
	 * store should be used.
	 *
	 * Structures are cached forever as they'll become stale when your
	 * application is deployed with changes. You can set a duration
	 * in seconds if you want the cache to clear after a certain
	 * timeframe.
	 */
	'structure_caching' => [
		'enabled' => true,
		'directories' => [app_path('Data')],
		'cache' => [
			'store' => env('CACHE_STORE', env('CACHE_DRIVER', 'file')),
			'prefix' => 'laravel-data',
			'duration' => null,
		],
		'reflection_discovery' => [
			'enabled' => true,
			'base_path' => base_path(),
			'root_namespace' => null,
		],
	],

	/**
	 * A data object can be validated when created using a factory or when calling the from
	 * method. By default, only when a request is passed the data is being validated. This
	 * behaviour can be changed to always validate or to completely disable validation.
	 */
	'validation_strategy' => \Spatie\LaravelData\Support\Creation\ValidationStrategy::OnlyRequests->value,

	/**
	 * When using an invalid include, exclude, only or except partial, the package will
	 * throw an exception. You can disable this behaviour by setting this option to true.
	 */
	'ignore_invalid_partials' => false,

	/**
	 * When transforming a nested chain of data objects, the package can end up in an infinite
	 * loop when including a recursive relationship. The max transformation depth can be
	 * set as a safety measure to prevent this from happening. When set to null, the
	 * package will not enforce a maximum depth.
	 */
	'max_transformation_depth' => null,

	/**
	 * When the maximum transformation depth is reached, the package will throw an exception.
	 * You can disable this behaviour by setting this option to true which will return an
	 * empty array.
	 */
	'throw_when_max_transformation_depth_reached' => true,

	/**
	 * When using the `make:data` command, the package will use these settings to generate
	 * the data classes. You can override these settings by passing options to the command.
	 */
	'commands' => [
		/**
		 * Provides default configuration for the `make:data` command. These settings can be overridden with options
		 * passed directly to the `make:data` command for generating single Data classes, or if not set they will
		 * automatically fall back to these defaults. See `php artisan make:data --help` for more information.
		 */
		'make' => [
			/**
			 * The default namespace for generated Data classes. This exists under the application's root namespace,
			 * so the default 'Data` will end up as '\App\Data', and generated Data classes will be placed in the
			 * app/Data/ folder. Data classes can live anywhere, but this is where `make:data` will put them.
			 */
			'namespace' => 'Data',

			/**
			 * This suffix will be appended to all data classes generated by make:data, so that they are less likely
			 * to conflict with other related classes, controllers or models with a similar name without resorting
			 * to adding an alias for the Data object. Set to a blank string (not null) to disable.
			 */
			'suffix' => 'Resource',
		],
	],

	/**
	 * When using Livewire, the package allows you to enable or disable the synths
	 * these synths will automatically handle the data objects and their
	 * properties when used in a Livewire component.
	 */
	'livewire' => [
		'enable_synths' => false,
	],
];
