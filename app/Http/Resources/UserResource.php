<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
	 */
	public function toArray($request)
	{
		/** @var User $user */
		$user = $this->resource;

		return [
			'id' => $user->id,
			'has_token' => $user->has_token,
			'username' => $user->username,
			'email' => $user->email,
		];
	}
}
