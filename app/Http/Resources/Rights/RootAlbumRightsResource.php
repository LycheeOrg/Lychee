<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Rights;

use App\Contracts\Models\AbstractAlbum;
use App\Models\LiveMetrics;
use App\Policies\AlbumPolicy;
use App\Policies\MetricsPolicy;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class RootAlbumRightsResource extends Data
{
	public bool $can_edit;
	public bool $can_upload;
	public bool $can_see_live_metrics;

	public function __construct()
	{
		$this->can_edit = Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, null]);
		$this->can_upload = Gate::check(AlbumPolicy::CAN_UPLOAD, [AbstractAlbum::class, null]);
		$this->can_see_live_metrics = Gate::check(MetricsPolicy::CAN_SEE_LIVE, LiveMetrics::class);
	}
}
