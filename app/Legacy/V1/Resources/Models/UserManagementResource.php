<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Resources\Models;

use App\Exceptions\Internal\LycheeLogicException;
use App\Legacy\V1\Resources\Traits\WithStatus;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Format a User for user management tasks, only give the required info.
 */
final class UserManagementResource extends JsonResource
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
	 * @return array<string,mixed>|\Illuminate\Contracts\Support\Arrayable<string,mixed>|\JsonSerializable
	 */
	public function toArray($request)
	{
		if ($this->resource === null) {
			// @codeCoverageIgnoreStart
			throw new LycheeLogicException('Trying to convert a null user into an array.');
			// @codeCoverageIgnoreEnd
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
