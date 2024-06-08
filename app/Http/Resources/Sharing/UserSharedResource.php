<?php

namespace App\Http\Resources\Sharing;

use App\Actions\Sharing\UserShared;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSharedResource extends JsonResource
{
	/**
	 * @param UserShared $user
	 *
	 * @return void
	 */
	public function __construct(object $user)
	{
		parent::__construct($user);
	}

	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return array<string,string|int>
	 */
	public function toArray($request): array
	{
		return [
			'id' => $this->resource->id,
			'username' => $this->resource->username,
		];
	}
}
