<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Resources;

use App\Legacy\V1\Resources\Models\UserResource;
use App\Legacy\V1\Resources\Rights\GlobalRightsResource;
use App\Metadata\Versions\FileVersion;
use App\Metadata\Versions\GitHubVersion;
use App\Models\Configs;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

final class InitResource extends JsonResource
{
	public function __construct()
	{
		// Laravel applies a shortcut when this value === null but not when it is something else.
		parent::__construct('must_not_be_null');
	}

	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return array<string,mixed>
	 */
	public function toArray($request): array
	{
		$fileVersion = resolve(FileVersion::class);
		$gitHubVersion = resolve(GitHubVersion::class);

		if (Configs::getValueAsBool('check_for_updates')) {
			// @codeCoverageIgnoreStart
			$fileVersion->hydrate();
			$gitHubVersion->hydrate();
			// @codeCoverageIgnoreEnd
		}

		// we also return the locale
		$locale = include base_path('lang/' . app()->getLocale() . '/lychee.php');

		return [
			'user' => $this->when(Auth::check(), UserResource::make(Auth::user()), null),
			'rights' => GlobalRightsResource::make(),
			'config' => ConfigurationResource::make(),
			'update_json' => !$fileVersion->isUpToDate(),
			'update_available' => !$gitHubVersion->isUpToDate(),
			'locale' => $locale,
		];
	}
}
