<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Sharing;

use App\Contracts\Http\Requests\HasAccessPermissionResource;
use App\Contracts\Http\Requests\HasAlbumIds;
use App\Contracts\Http\Requests\HasUserGroupIds;
use App\Contracts\Http\Requests\HasUserIds;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAccessPermissionResourceTrait;
use App\Http\Requests\Traits\HasAlbumIdsTrait;
use App\Http\Requests\Traits\HasUserGroupIdsTrait;
use App\Http\Requests\Traits\HasUserIdsTrait;
use App\Http\Resources\Models\AccessPermissionResource;
use App\Policies\AlbumPolicy;
use App\Rules\IntegerIDRule;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

/**
 * Represents a request for setting the shares of specific albums.
 *
 * Only the owner of the album (or the admin) can set the shares.
 */
class AddSharingRequest extends BaseApiRequest implements HasAlbumIds, HasUserIds, HasAccessPermissionResource, HasUserGroupIds
{
	use HasAlbumIdsTrait;
	use HasUserIdsTrait;
	use HasUserGroupIdsTrait;
	use HasAccessPermissionResourceTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(AlbumPolicy::CAN_SHARE_ID, [AbstractAlbum::class, $this->albumIds()]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_IDS_ATTRIBUTE => 'required|array|min:1',
			RequestAttribute::ALBUM_IDS_ATTRIBUTE . '.*' => ['required', new RandomIDRule(false)],
			RequestAttribute::USER_IDS_ATTRIBUTE => 'present|array',
			RequestAttribute::USER_IDS_ATTRIBUTE . '.*' => ['required', new IntegerIDRule(false)],
			RequestAttribute::USER_GROUP_IDS_ATTRIBUTE => 'present|array',
			RequestAttribute::USER_GROUP_IDS_ATTRIBUTE . '.*' => ['required', new IntegerIDRule(false)],
			RequestAttribute::GRANTS_DOWNLOAD_ATTRIBUTE => ['required', 'boolean'],
			RequestAttribute::GRANTS_FULL_PHOTO_ACCESS_ATTRIBUTE => ['required', 'boolean'],
			RequestAttribute::GRANTS_UPLOAD_ATTRIBUTE => ['required', 'boolean'],
			RequestAttribute::GRANTS_EDIT_ATTRIBUTE => ['required', 'boolean'],
			RequestAttribute::GRANTS_DELETE_ATTRIBUTE => ['required', 'boolean'],
		];
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws ValidationException if no users or groups are specified
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album_ids = $values[RequestAttribute::ALBUM_IDS_ATTRIBUTE];
		$this->user_ids = $values[RequestAttribute::USER_IDS_ATTRIBUTE];
		$this->user_group_ids = $values[RequestAttribute::USER_GROUP_IDS_ATTRIBUTE];

		if ($this->user_ids === [] && $this->user_group_ids === []) {
			// If no users or groups are specified, we do not create a permission.
			throw new ValidationException('You must specify at least one user or group to share with.', 422);
		}

		$this->perm_resource = new AccessPermissionResource(
			grants_edit: static::toBoolean($values[RequestAttribute::GRANTS_EDIT_ATTRIBUTE]),
			grants_delete: static::toBoolean($values[RequestAttribute::GRANTS_DELETE_ATTRIBUTE]),
			grants_download: static::toBoolean($values[RequestAttribute::GRANTS_DOWNLOAD_ATTRIBUTE]),
			grants_full_photo_access: static::toBoolean($values[RequestAttribute::GRANTS_FULL_PHOTO_ACCESS_ATTRIBUTE]),
			grants_upload: static::toBoolean($values[RequestAttribute::GRANTS_UPLOAD_ATTRIBUTE]),
		);
	}
}