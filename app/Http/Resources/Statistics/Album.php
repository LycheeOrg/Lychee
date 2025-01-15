<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Statistics;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class Album extends Data
{
	public string $username;
	public string $title;
	public bool $is_nsfw;
	public int $left;
	public int $right;
	public int $num_photos;
	public int $num_descendants;
	public int $size;

	/**
	 * @param array{id:string,left:int,right:int,size:int}                                                                     $space_data
	 * @param array{id:string,username:string,title:string,is_nsfw:bool,left:int,right:int,num_photos:int,num_descendants:int} $count_data
	 *
	 * @return void
	 */
	public function __construct(array $space_data, array $count_data)
	{
		$this->username = $count_data['username'];
		$this->title = $count_data['title'];
		$this->is_nsfw = $count_data['is_nsfw'];
		$this->left = $count_data['left'];
		$this->right = $count_data['right'];
		$this->num_photos = $count_data['num_photos'];
		$this->num_descendants = $count_data['num_descendants'];
		$this->size = $space_data['size'];
	}
}
