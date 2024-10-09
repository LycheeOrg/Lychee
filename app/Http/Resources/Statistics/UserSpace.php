<?php

namespace App\Http\Resources\Statistics;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class UserSpace extends Data
{
	public string $username;
	public int $size;

	/**
	 * @param array{username:string,size:int} $user_data
	 *
	 * @return void
	 */
	public function __construct(array $user_data)
	{
		$this->username = $user_data['username'];
		$this->size = $user_data['size'];
	}

	/**
	 * @param array{username:string,size:int} $user_data
	 *
	 * @return UserSpace
	 */
	public static function fromArray(array $user_data): self
	{
		return new self($user_data);
	}
}
