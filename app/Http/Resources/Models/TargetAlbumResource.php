<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @phpstan-type TAlbumSaved array{id:string|null,title:string,original:string,short_title:string,thumb:string}
 */
#[TypeScript()]
class TargetAlbumResource extends Data
{
	public ?string $id;
	public string $title;
	public string $original;
	public string $short_title;
	public string $thumb;

	/**
	 * @param TAlbumSaved $values
	 *
	 * @return void
	 */
	public function __construct(array $values)
	{
		$this->id = $values['id'];
		$this->title = $values['title'];
		$this->original = $values['original'];
		$this->short_title = $values['short_title'];
		$this->thumb = $values['thumb'];
	}

	/**
	 * @param TAlbumSaved $a
	 *
	 * @return TargetAlbumResource
	 */
	public static function fromArray(array $a): TargetAlbumResource
	{
		return new self($a);
	}
}
