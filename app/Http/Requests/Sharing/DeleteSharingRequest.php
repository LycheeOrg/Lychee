<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Sharing;

use App\Contracts\Http\Requests\HasAccessPermission;
use App\Contracts\Http\Requests\HasAlbumIds;
use App\Contracts\Http\Requests\HasUserIds;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAccessPermissionTrait;
use App\Http\Requests\Traits\HasAlbumIdsTrait;
use App\Http\Requests\Traits\HasUserIdsTrait;
use App\Models\AccessPermission;
use App\Policies\AlbumPolicy;
use App\Rules\IntegerIDRule;
use Illuminate\Support\Facades\Gate;

/**
 * Represents a request for deleting the shares of specific albums.
 *
 * Only the owner of the album (or the admin) can set the shares.
 */
class DeleteSharingRequest extends BaseApiRequest implements HasAlbumIds, HasUserIds, HasAccessPermission
{
	use HasAlbumIdsTrait;
	use HasUserIdsTrait;
	use HasAccessPermissionTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(AlbumPolicy::CAN_SHARE_WITH_USERS, [AbstractAlbum::class, $this->perm->album]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::PERMISSION_ID => ['required', new IntegerIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var int $id */
		$id = $values[RequestAttribute::PERMISSION_ID];
		$this->perm = AccessPermission::with(['album', 'user'])->findOrFail($id);
	}
}