<?php

namespace App\Http\Resources\Sharing;

use Spatie\LaravelData\Data;

class UserSharedResource extends Data
{
	public int $id;
	public string $username;

	/**
	 * @param object{id:int,username:string} $user
	 */
	public function __construct(object $user)
	{
		$this->id = $user->id;
		$this->username = $user->username;
	}

	/**
	 * @param object{id:int,username:string} $user
	 *
	 * @return UserSharedResource
	 */
	public static function fromModel(object $user): UserSharedResource
	{
		return new UserSharedResource($user);
	}
}
