<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Sharing;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class SharedAlbumResource extends Data
{
	public int $id;
	public int $user_id;
	public string $album_id;
	public string $username;
	public string $title;

	/**
	 * @param object{id:int,user_id:int,album_id:string,username:string,title:string} $album_shared
	 *
	 * @return void
	 */
	public function __construct(object $album_shared)
	{
		$this->id = $album_shared->id;
		$this->user_id = $album_shared->user_id;
		$this->album_id = $album_shared->album_id;
		$this->username = $album_shared->username;
		$this->title = $album_shared->title;
	}
}