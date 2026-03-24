<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * A lightweight summary of one face cluster for the cluster-review UI.
 */
#[TypeScript()]
class ClusterPreviewResource extends Data
{
	public int $cluster_label;
	public int $face_count;

	/** @var list<string> Up to 5 sample crop URLs for preview thumbnails. */
	public array $sample_crop_urls;

	public function __construct(int $cluster_label, int $face_count, array $sample_crop_urls)
	{
		$this->cluster_label = $cluster_label;
		$this->face_count = $face_count;
		$this->sample_crop_urls = $sample_crop_urls;
	}
}
