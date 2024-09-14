<?php

namespace App\Http\Resources\Collections;

use App\DTO\TopAlbumDTO;
use App\Http\Resources\Models\AlbumResource;
use App\Http\Resources\Models\SmartAlbumResource;
use App\Http\Resources\Models\TagAlbumResource;
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
class FullRootAlbumResource extends Data
{
	/** @var Collection<int,SmartAlbumResource> */
	public Collection $smart_albums;
	/** @var Collection<int,TagAlbumResource> */
	public Collection $tag_albums;
	/** @var Collection<int,AlbumResource> */
	public Collection $albums;
	/** @var Collection<int,AlbumResource> */
	public ?Collection $shared_albums = null;

	/**
	 * @param Collection<int,SmartAlbumResource> $smart_albums
	 * @param Collection<int,TagAlbumResource>   $tag_albums
	 * @param Collection<int,AlbumResource>      $albums
	 * @param Collection<int,AlbumResource>|null $shared_albums
	 *
	 * @return void
	 */
	public function __construct(
		Collection $smart_albums,
		Collection $tag_albums,
		Collection $albums,
		?Collection $shared_albums = null,
	) {
		$this->smart_albums = $smart_albums;
		$this->tag_albums = $tag_albums;
		$this->albums = $albums;
		$this->shared_albums = $shared_albums;
	}

	public static function fromDTO(TopAlbumDTO $dto): self
	{
		return new self(
			smart_albums: SmartAlbumResource::collect($dto->smart_albums),
			tag_albums: TagAlbumResource::collect($dto->tag_albums),
			albums: AlbumResource::collect($dto->albums),
			shared_albums: $dto->shared_albums !== null ? AlbumResource::collect($dto->shared_albums) : null,
		);
	}
}