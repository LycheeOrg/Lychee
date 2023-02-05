<?php

namespace App\Http\Resources\Models;

use App\Exceptions\Internal\LycheeLogicException;
use App\Http\Resources\Traits\WithStatus;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Format a User for user management tasks, only give the required info.
 */
class UserManagementResource extends JsonResource
{
	use WithStatus;

	public function __construct(User $user)
	{
		parent::__construct($user);
	}

	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
	 */
	public function toArray($request)
	{
		if ($this->resource === null) {
			throw new LycheeLogicException('Trying to convert a null user into an array.');
		}

		return [
			'id' => $this->resource->id,
			'username' => $this->resource->username,
			'may_administrate' => $this->resource->may_administrate,
			'may_upload' => $this->resource->may_upload,
			'may_edit_own_settings' => $this->resource->may_edit_own_settings,
		];
	}
}
