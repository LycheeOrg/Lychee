<?php

namespace App\Http\Resources\Collections;

use App\Http\Resources\Models\AlbumResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Provide stronger typechecking for Album collections.
 */
class AlbumCollectionResource extends ResourceCollection
{
	/**
	 * The resource that this resource collects.
	 *
	 * @var string
	 */
	public $collects = AlbumResource::class;
}
