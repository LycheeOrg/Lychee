<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Http\Resources\Models\Utils\AlbumProtectionPolicy;
use App\Http\Resources\Models\Utils\PreFormattedAlbumData;
use App\Http\Resources\Rights\AlbumRightsResource;
use App\Http\Resources\Traits\HasHeaderUrl;
use App\SmartAlbums\BaseSmartAlbum;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class HeadSmartAlbumResource extends Data
{
	use HasHeaderUrl;

	public string $id;
	public string $title;
	public AlbumProtectionPolicy $policy;
	public AlbumRightsResource $rights;
	public PreFormattedAlbumData $preFormattedData;
	public null $statistics = null; // Needed to unify the API response with the AlbumResource and TagAlbumResource.

	public function __construct(BaseSmartAlbum $smart_album)
	{
		$this->id = $smart_album->get_id();
		$this->title = $smart_album->get_title();

		/** @disregard P1006 */
		$this->policy = AlbumProtectionPolicy::ofSmartAlbum($smart_album);
		$this->rights = new AlbumRightsResource($smart_album);
		$url = $this->getHeaderUrl($smart_album);
		$this->preFormattedData = new PreFormattedAlbumData($smart_album, $url);
	}

	public static function fromModel(BaseSmartAlbum $smart_album): HeadSmartAlbumResource
	{
		return new self($smart_album);
	}
}
