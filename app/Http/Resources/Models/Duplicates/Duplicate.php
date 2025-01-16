<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Models\Duplicates;

use App\Enum\SizeVariantType;
use App\Models\Extensions\HasUrlGenerator;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class Duplicate extends Data
{
	use HasUrlGenerator;

	public function __construct(
		public string $album_id,
		public string $album_title,
		public string $photo_id,
		public string $photo_title,
		public string $checksum,
		public ?string $url,
	) {
	}

	/**
	 * @param object{album_id:string,album_title:string,photo_id:string,photo_title:string,checksum:string,short_path:string|null,storage_disk:string|null} $model
	 *
	 * @return Duplicate
	 */
	public static function fromModel(object $model): Duplicate
	{
		return new Duplicate(
			album_id: $model->album_id,
			album_title: $model->album_title,
			photo_id: $model->photo_id,
			photo_title: $model->photo_title,
			checksum: $model->checksum,
			url: $model->short_path === null ? null : self::pathToUrl($model->short_path, $model->storage_disk, SizeVariantType::SMALL),
		);
	}
}