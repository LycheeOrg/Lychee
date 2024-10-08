<?php

namespace App\Http\Resources\Statistics;

use App\Enum\SizeVariantType;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class Statistics extends Data
{
	/** @var Collection<int,Sizes> */
	public Collection $sizes;
	/** @var Collection<int,Album> */
	public Collection $albums;
	/** @var Collection<int,Album> */
	public Collection $collapsed_albums;

	/**
	 * @param Collection<int,Sizes> $sizes
	 * @param Collection<int,Album> $albums
	 * @param Collection<int,Album> $collapsed_albums
	 *
	 * @return void
	 */
	public function __construct(Collection $sizes, Collection $albums, Collection $collapsed_albums)
	{
		$this->sizes = $sizes;
		$this->albums = $albums;
		$this->collapsed_albums = $collapsed_albums;
	}

	/**
	 * @param Collection<int,array{type:SizeVariantType,size:int}>                                                                            $sizes
	 * @param Collection<int,array{username:string,title:string,is_nsfw:bool,left:int,right:int,num_photos:int,num_descendants:int,size:int}> $albums
	 * @param Collection<int,array{username:string,title:string,is_nsfw:bool,left:int,right:int,num_photos:int,num_descendants:int,size:int}> $collapsed_albums
	 *
	 * @return Statistics
	 */
	public static function fromDTO(Collection $sizes, Collection $albums, Collection $collapsed_albums): self
	{
		return new self(
			sizes: Sizes::collect($sizes),
			albums: Album::collect($albums),
			collapsed_albums: Album::collect($collapsed_albums)
		);
	}
}
