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
	 * @param object{id:int,user_id:int,album_id:string,username:string,title:string} $albumShared
	 *
	 * @return void
	 */
	public function __construct(object $albumShared)
	{
		$this->id = $albumShared->id;
		$this->user_id = $albumShared->user_id;
		$this->album_id = $albumShared->album_id;
		$this->username = $albumShared->username;
		$this->title = $albumShared->title;
	}
}
