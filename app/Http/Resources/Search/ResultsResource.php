<?php

namespace App\Http\Resources\Search;

use App\Http\Resources\Models\AlbumResource;
use App\Http\Resources\Models\PhotoResource;
use App\Models\Album;
use App\Models\Photo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
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
class ResultsResource extends Data
{
	/** @var Collection<int,AlbumResource> */
	public Collection $albums;

	/** @var Paginator<PhotoResource> */
	public Paginator $photos;

	/**
	 * @param Collection<int,AlbumResource> $albums
	 * @param Paginator<PhotoResource>      $photos
	 *
	 * @return void
	 */
	public function __construct(
		Collection $albums,
		Paginator $photos,
	) {
		$this->albums = $albums;
		$this->photos = $photos;
	}

	/**
	 * @param Collection<int,Album>       $albums
	 * @param LengthAwarePaginator<Photo> $photos
	 *
	 * @return ResultsResource
	 */
	public static function fromData(Collection $albums, LengthAwarePaginator $photos): self
	{
		return new self(
			albums: AlbumResource::collect($albums),
			photos: PhotoResource::collect($photos), // @phpstan-ignore-line
		);
	}
}