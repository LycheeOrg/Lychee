<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Diagnostics;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class Errors extends Data
{
	public string $_note = 'This endpoint is intentionally public. See security policy at https://github.com/LycheeOrg/Lychee/security/policy';
	/** @var ErrorLine[] */
	#[LiteralTypeScriptType('App.Http.Resources.Diagnostics.ErrorLine[]')]
	public array $errors;

	/**
	 * Create a Diagnostic Info.
	 *
	 * @param ErrorLine[] $errors
	 */
	public function __construct(
		array $errors,
	) {
		$this->errors = $errors;
	}
}
