<?php

namespace App\Http\Resources\Collections;

use App\DTO\TopAlbumDTO;
use App\Http\Resources\GalleryConfigs\RootConfig;
use App\Http\Resources\Models\ThumbAlbumResource;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Data Transfer Object (DTO) to transmit the top albums to the client.
 *
 * This DTO differentiates between albums which are owned by the user and
 * "shared" albums which the user does not own, but is allowed to see.
 * The term "shared album" might be a little misleading here.
 * Albums which are owned by the user himself may also be shared (with
 * other users.)
 * Actually, in this context "shared albums" means "foreign albums".
 */
#[TypeScript()]
class RootAlbumResource extends Data
{
	/** @var Collection<int,ThumbAlbumResource> */
	public Collection $smart_albums;
	/** @var Collection<int,ThumbAlbumResource> */
	public Collection $tag_albums;
	/** @var Collection<int,ThumbAlbumResource> */
	public Collection $albums;
	/** @var Collection<int,ThumbAlbumResource> */
	public Collection $shared_albums;
	public RootConfig $config;

	/**
	 * @param Collection<int,ThumbAlbumResource> $smart_albums
	 * @param Collection<int,ThumbAlbumResource> $tag_albums
	 * @param Collection<int,ThumbAlbumResource> $albums
	 * @param Collection<int,ThumbAlbumResource> $shared_albums
	 * @param RootConfig                         $config
	 *
	 * @return void
	 */
	public function __construct(
		Collection $smart_albums,
		Collection $tag_albums,
		Collection $albums,
		Collection $shared_albums,
		RootConfig $config,
	) {
		$this->smart_albums = $smart_albums;
		$this->tag_albums = $tag_albums;
		$this->albums = $albums;
		$this->shared_albums = $shared_albums;
		$this->config = $config;
	}

	public static function fromDTO(TopAlbumDTO $dto, RootConfig $config): self
	{
		return new self(
			smart_albums: ThumbAlbumResource::collect($dto->smart_albums->values()),
			tag_albums: ThumbAlbumResource::collect($dto->tag_albums),
			albums: ThumbAlbumResource::collect($dto->albums),
			shared_albums: $dto->shared_albums !== null ? ThumbAlbumResource::collect($dto->shared_albums) : collect([]),
			config: $config,
		);
	}
}