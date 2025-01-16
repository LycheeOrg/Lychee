<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Sharing;

use App\Contracts\Http\Requests\HasAccessPermission;
use App\Contracts\Http\Requests\HasAccessPermissionResource;
use App\Contracts\Http\Requests\HasAlbumIds;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAccessPermissionResourceTrait;
use App\Http\Requests\Traits\HasAccessPermissionTrait;
use App\Http\Requests\Traits\HasAlbumIdsTrait;
use App\Http\Resources\Models\AccessPermissionResource;
use App\Models\AccessPermission;
use App\Policies\AlbumPolicy;
use App\Rules\IntegerIDRule;
use Illuminate\Support\Facades\Gate;

/**
 * Represents a request for Editing the shares of specific albums.
 *
 * Only the owner of the album (or the admin) can set the shares.
 */
class EditSharingRequest extends BaseApiRequest implements HasAlbumIds, HasAccessPermission, HasAccessPermissionResource
{
	use HasAlbumIdsTrait;
	use HasAccessPermissionTrait;
	use HasAccessPermissionResourceTrait;

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
			RequestAttribute::GRANTS_DOWNLOAD_ATTRIBUTE => ['required', 'boolean'],
			RequestAttribute::GRANTS_FULL_PHOTO_ACCESS_ATTRIBUTE => ['required', 'boolean'],
			RequestAttribute::GRANTS_UPLOAD_ATTRIBUTE => ['required', 'boolean'],
			RequestAttribute::GRANTS_EDIT_ATTRIBUTE => ['required', 'boolean'],
			RequestAttribute::GRANTS_DELETE_ATTRIBUTE => ['required', 'boolean'],
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

		$this->permResource = new AccessPermissionResource(
			grants_edit: static::toBoolean($values[RequestAttribute::GRANTS_EDIT_ATTRIBUTE]),
			grants_delete: static::toBoolean($values[RequestAttribute::GRANTS_DELETE_ATTRIBUTE]),
			grants_download: static::toBoolean($values[RequestAttribute::GRANTS_DOWNLOAD_ATTRIBUTE]),
			grants_full_photo_access: static::toBoolean($values[RequestAttribute::GRANTS_FULL_PHOTO_ACCESS_ATTRIBUTE]),
			grants_upload: static::toBoolean($values[RequestAttribute::GRANTS_UPLOAD_ATTRIBUTE]),
		);
	}
}