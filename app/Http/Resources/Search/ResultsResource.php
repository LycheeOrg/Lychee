<?php

namespace App\Http\Resources\Search;

use App\Http\Resources\Models\PhotoResource;
use App\Http\Resources\Models\ThumbAlbumResource;
use App\Http\Resources\Traits\HasPrepPhotoCollection;
use App\Models\Album;
use App\Models\Photo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
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
	use HasPrepPhotoCollection;

	/** @var Collection<int,ThumbAlbumResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Models.ThumbAlbumResource[]')]
	public Collection $albums;

	/** @var Collection<int,PhotoResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Models.PhotoResource[]')]
	public Collection $photos;

	public int $current_page;
	public int $from;
	public int $last_page;
	public int $per_page;
	public int $to;
	public int $total;

	/**
	 * @param Collection<int,ThumbAlbumResource>                           $albums
	 * @param LengthAwarePaginator<PhotoResource>&Paginator<PhotoResource> $photos
	 *
	 * @return void
	 */
	public function __construct(
		Collection $albums,
		LengthAwarePaginator $photos,
	) {
		$this->albums = $albums;
		$this->photos = collect($photos->items());
		$this->current_page = $photos->currentPage();
		$this->from = $photos->firstItem();
		$this->last_page = $photos->lastPage();
		$this->per_page = $photos->perPage();
		$this->to = $photos->lastItem();
		$this->total = $photos->total();

		$this->prepPhotosCollection();
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
			albums: ThumbAlbumResource::collect($albums),
			photos: PhotoResource::collect($photos), // @phpstan-ignore-line
		);
	}
}