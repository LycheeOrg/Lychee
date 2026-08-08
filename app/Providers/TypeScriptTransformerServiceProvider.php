<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Providers;

use Spatie\LaravelTypeScriptTransformer\LaravelData\LaravelDataTypeScriptTransformerExtension;
use Spatie\LaravelTypeScriptTransformer\TypeScriptTransformerApplicationServiceProvider as BaseTypeScriptTransformerServiceProvider;
use Spatie\TypeScriptTransformer\Formatters\PrettierFormatter;
use Spatie\TypeScriptTransformer\Transformers\AttributedClassTransformer;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;
use Spatie\TypeScriptTransformer\Writers\GlobalNamespaceWriter;

class TypeScriptTransformerServiceProvider extends BaseTypeScriptTransformerServiceProvider
{
	protected function configure(TypeScriptTransformerConfigFactory $config): void
	{
		$config->extension(new LaravelDataTypeScriptTransformerExtension())
				->transformer(AttributedClassTransformer::class)
				->transformer(EnumTransformer::class)
				->transformDirectories(app_path())
				->outputDirectory(resource_path('js'))
				->writer(new GlobalNamespaceWriter('lychee.d.ts'))
				->formatter(PrettierFormatter::class);
	}
}
