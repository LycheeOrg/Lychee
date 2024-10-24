<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\Editable\EditableBaseAlbumResource;
use App\Http\Resources\Models\Utils\AlbumProtectionPolicy;
use App\Http\Resources\Models\Utils\PreFormattedAlbumData;
use App\Http\Resources\Rights\AlbumRightsResource;
use App\Http\Resources\Traits\HasHeaderUrl;
use App\Http\Resources\Traits\HasPrepPhotoCollection;
use App\Models\Album;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class AlbumResource extends Data
{
	use HasPrepPhotoCollection;
	use HasHeaderUrl;

	public string $id;
	public string $title;
	public ?string $owner_name;
	public ?string $description;
	public ?string $copyright;

	// attributes
	public ?string $track_url;
	public string $license;
	public ?string $header_id;

	// children
	public ?string $parent_id;
	public bool $has_albums;
	/** @var ?Collection<int,ThumbAlbumResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Models.ThumbAlbumResource[]')]
	public ?Collection $albums;
	/** @var ?Collection<int,PhotoResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Models.PhotoResource[]')]
	public ?Collection $photos;

	// thumb
	public ?string $cover_id;
	public ?ThumbResource $thumb;

	// security
	public AlbumProtectionPolicy $policy;
	public AlbumRightsResource $rights;
	public PreFormattedAlbumData $preFormattedData;
	public ?EditableBaseAlbumResource $editable;

	public function __construct(Album $album)
	{
		$this->id = $album->id;
		$this->title = $album->title;
		$this->description = $album->description;
		$this->owner_name = Auth::check() ? $album->owner->name : null;
		$this->copyright = $album->copyright;

		// attributes
		$this->track_url = $album->track_url;
		$this->license = $album->license->localization();
		// TODO: Investigate later why this string is 24 characters long.
		$this->header_id = trim($album->header_id);

		// children
		$this->parent_id = $album->parent_id;
		$this->has_albums = !$album->isLeaf();
		$this->albums = $album->relationLoaded('children') ? ThumbAlbumResource::collect($album->children) : null;
		$this->photos = $album->relationLoaded('photos') ? PhotoResource::collect($album->photos) : null;
		$this->prepPhotosCollection();

		// thumb
		$this->cover_id = $album->cover_id;
		$this->thumb = ThumbResource::make($album->thumb?->id, $album->thumb?->type, $album->thumb?->thumbUrl, $album->thumb?->thumb2xUrl);

		// security
		$this->policy = AlbumProtectionPolicy::ofBaseAlbum($album);
		$this->rights = new AlbumRightsResource($album);
		$url = $this->getHeaderUrl($album);
		$this->preFormattedData = new PreFormattedAlbumData($album, $url);

		if ($this->rights->can_edit) {
			$this->editable = EditableBaseAlbumResource::fromModel($album);
		}
	}

	public static function fromModel(Album $album): AlbumResource
	{
		return new self($album);
	}
}
