<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Flow;

use App\Models\Configs;
use Illuminate\Support\Facades\Auth;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Result of a Search query.
 */
#[TypeScript()]
class InitResource extends Data
{
	public bool $is_mod_flow_enabled;

	/**
	 * @return void
	 */
	public function __construct(
	) {
		$this->is_mod_flow_enabled = Configs::getValueAsBool('flow_enabled') && (Auth::check() || Configs::getValueAsBool('flow_public'));
	}
}