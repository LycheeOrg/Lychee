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
use App\Models\PersonAlbum;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class HeadPersonAlbumResource extends Data
{
	use HasHeaderUrl;

	public string $id;
	public string $title;
	public ?string $slug;
	public ?string $owner_name;
	public ?string $copyright;
	public bool $is_person_album;

	/** @var array<int,array{id:string,name:string}> */
	public array $show_persons;

	// security
	public AlbumProtectionPolicy $policy;
	public AlbumRightsResource $rights;
	public PreFormattedAlbumData $preFormattedData;
	public ?EditableBaseAlbumResource $editable;

	public ?AlbumStatisticsResource $statistics = null;

	public function __construct(PersonAlbum $person_album)
	{
		$this->id = $person_album->id;
		$this->title = $person_album->title;
		$this->slug = request()->verify()->is_supporter() ? $person_album->slug : null;
		$this->owner_name = Auth::check() ? $person_album->owner->name : null;
		$this->is_person_album = true;
		$this->copyright = $person_album->copyright;

		$user_id = Auth::id();
		$this->show_persons = $person_album->persons
			->filter(fn ($p) => $p->is_searchable || ($user_id !== null && $p->user_id === $user_id))
			->map(fn ($p) => ['id' => $p->id, 'name' => $p->name])
			->values()
			->all();

		// security
		$this->policy = AlbumProtectionPolicy::ofBaseAlbum($person_album);
		$this->rights = new AlbumRightsResource($person_album);
		$url = $this->getHeaderUrl($person_album);
		$this->preFormattedData = new PreFormattedAlbumData($person_album, $url);

		if ($this->rights->can_edit) {
			$this->editable = EditableBaseAlbumResource::fromModel($person_album);
		}

		if (request()->configs()->getValueAsBool('metrics_enabled') && Gate::check(AlbumPolicy::CAN_READ_METRICS, [PersonAlbum::class, $person_album])) {
			$this->statistics = AlbumStatisticsResource::fromModel($person_album->statistics);
		}
	}

	public static function fromModel(PersonAlbum $person_album): HeadPersonAlbumResource
	{
		return new self($person_album);
	}
}
