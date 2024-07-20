<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\GalleryConfigs\AlbumConfig;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class AbstractAlbumResource extends Data
{
	public AlbumConfig $config;
	public AlbumResource|SmartAlbumResource|TagAlbumResource|null $resource;

	public function __construct(AlbumConfig $config, AlbumResource|SmartAlbumResource|TagAlbumResource|null $resource)
	{
		$this->config = $config;
		$this->resource = $resource;
	}
}
