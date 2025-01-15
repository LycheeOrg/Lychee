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
class UserSpace extends Data
{
	public int $id;
	public string $username;
	public int $size;

	/**
	 * @param array{id:int,username:string,size:int} $user_data
	 *
	 * @return void
	 */
	public function __construct(array $user_data)
	{
		$this->id = $user_data['id'];
		$this->username = $user_data['username'];
		$this->size = $user_data['size'];
	}

	/**
	 * @param array{id:int,username:string,size:int} $user_data
	 *
	 * @return UserSpace
	 */
	public static function fromArray(array $user_data): self
	{
		return new self($user_data);
	}
}
