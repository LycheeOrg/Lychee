<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\GalleryConfigs;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class SettingsConfig extends Data
{
	public bool $default_old_settings;
	public bool $default_expert_settings;
	public bool $default_all_settings;

	public function __construct()
	{
		$this->default_old_settings = request()->configs()->getValueAsBool('default_old_settings');
		$this->default_expert_settings = request()->configs()->getValueAsBool('default_expert_settings');
		$this->default_all_settings = request()->configs()->getValueAsBool('default_all_settings');
	}
}
