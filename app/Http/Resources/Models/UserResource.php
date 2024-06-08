<?php

namespace App\Http\Resources\Models;

use App\Exceptions\Internal\LycheeLogicException;
use App\Http\Resources\Traits\WithStatus;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Format a User for their own profile.
 */
class UserResource extends JsonResource
{
	use WithStatus;

	public function __construct(?User $user)
	{
		parent::__construct($user);
	}

	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return array<string,mixed>|\Illuminate\Contracts\Support\Arrayable<string,mixed>|\JsonSerializable
	 */
	public function toArray($request)
	{
		if ($this->resource === null) {
			throw new LycheeLogicException('Trying to convert a null user into an array.');
		}

		return [
			'id' => $this->resource->id,
			'has_token' => $this->resource->token !== null,
			'username' => $this->resource->username,
			'email' => $this->resource->email,
		];
	}
}
