<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Http\Resources\Editable\EditableBaseAlbumResource;
use App\Http\Resources\Models\Utils\AlbumProtectionPolicy;
use App\Http\Resources\Models\Utils\PreFormattedAlbumData;
use App\Http\Resources\Rights\AlbumRightsResource;
use App\Http\Resources\Traits\HasHeaderUrl;
use App\Models\Album;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class HeadAlbumResource extends Data
{
	use HasHeaderUrl;

	public string $id;
	public string $title;
	public ?string $slug;
	public ?string $owner_name;
	public ?string $description;
	public ?string $copyright;

	// attributes
	public ?string $track_url;
	public string $license;
	public ?string $header_id;

	// children counts (no actual children/photos arrays)
	public ?string $parent_id;
	public bool $has_albums;
	public int $num_children;
	public int $num_photos;

	// thumb
	public ?string $cover_id;

	// security
	public AlbumProtectionPolicy $policy;
	public AlbumRightsResource $rights;
	public PreFormattedAlbumData $preFormattedData;
	public ?EditableBaseAlbumResource $editable;
	public bool $is_pinned;

	public ?AlbumStatisticsResource $statistics = null;

	public function __construct(Album $album)
	{
		$this->id = $album->id;
		$this->title = $album->title;
		$this->slug = request()->verify()->is_supporter() ? $album->slug : null;
		$this->description = $album->description;
		$this->owner_name = Auth::check() ? $album->owner->name : null;
		$this->copyright = $album->copyright;

		// attributes
		$this->track_url = $album->track_url;
		$this->license = $album->license->localization();
		// TODO: Investigate later why this string is 24 characters long.
		$this->header_id = $album->header_id !== null ? trim($album->header_id) : null;

		// children counts only
		$this->parent_id = $album->parent_id;
		$this->has_albums = !$album->isLeaf();
		$this->num_children = $album->num_children;
		$this->num_photos = $album->num_photos;

		// thumb
		$this->cover_id = $album->cover_id;

		// security
		$this->policy = AlbumProtectionPolicy::ofBaseAlbum($album);
		$this->rights = new AlbumRightsResource($album);
		$url = $this->getHeaderUrl($album);
		$this->preFormattedData = new PreFormattedAlbumData($album, $url);
		$this->is_pinned = $album->is_pinned;

		if ($this->rights->can_edit) {
			$this->editable = EditableBaseAlbumResource::fromModel($album);
		}

		if (request()->configs()->getValueAsBool('metrics_enabled') && Gate::check(AlbumPolicy::CAN_READ_METRICS, [Album::class, $album])) {
			$this->statistics = AlbumStatisticsResource::fromModel($album->statistics);
		}
	}

	public static function fromModel(Album $album): HeadAlbumResource
	{
		return new self($album);
	}
}
