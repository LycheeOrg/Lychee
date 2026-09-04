<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\GalleryConfigs;

use App\Repositories\ConfigManager;
use App\Services\TemporaryLinkSigner;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class TemporaryLinkMacConfig extends Data
{
	public string $mac;

	public function __construct()
	{
		$configs = resolve(ConfigManager::class);
		$signer = resolve(TemporaryLinkSigner::class);
		$this->mac = $configs->getValueAsBool('temporary_image_link_enabled')
			? $signer->sign()
			: '';
	}
}
