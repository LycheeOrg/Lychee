<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Sharing;

use App\Constants\AccessPermissionConstants as APC;
use App\Contracts\Http\Requests\HasAccessPermission;
use App\Contracts\Http\Requests\HasAccessPermissionResource;
use App\Contracts\Http\Requests\HasAlbumIds;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAccessPermissionResourceTrait;
use App\Http\Requests\Traits\HasAccessPermissionTrait;
use App\Http\Requests\Traits\HasAlbumIdsTrait;
use App\Http\Requests\Traits\HasHeldGrantsTrait;
use App\Http\Resources\Models\AccessPermissionResource;
use App\Models\AccessPermission;
use App\Models\User;
use App\Policies\AlbumPolicy;
use App\Rules\HeldGrantRule;
use App\Rules\IntegerIDRule;
use Illuminate\Support\Facades\Auth;
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
	use HasHeldGrantsTrait;

	/**
	 * The permission being edited, read once in {@see self::rules()} and
	 * reused by {@see self::processValidatedValues()}.
	 */
	private ?AccessPermission $stored_perm = null;

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
		$this->stored_perm = $this->findStoredPermission();

		return [
			RequestAttribute::PERMISSION_ID => ['required', new IntegerIDRule(false)],
			RequestAttribute::GRANTS_DOWNLOAD_ATTRIBUTE => ['required', 'boolean'],
			RequestAttribute::GRANTS_FULL_PHOTO_ACCESS_ATTRIBUTE => ['required', 'boolean'],
			RequestAttribute::GRANTS_UPLOAD_ATTRIBUTE => ['required', 'boolean', new HeldGrantRule($this->holdsUploadGrant(), $this->alreadyGranted(APC::GRANTS_UPLOAD))],
			RequestAttribute::GRANTS_EDIT_ATTRIBUTE => ['required', 'boolean', new HeldGrantRule($this->holdsEditGrants(), $this->alreadyGranted(APC::GRANTS_EDIT))],
			RequestAttribute::GRANTS_DELETE_ATTRIBUTE => ['required', 'boolean', new HeldGrantRule($this->holdsEditGrants(), $this->alreadyGranted(APC::GRANTS_DELETE))],
			RequestAttribute::GRANTS_MOVE_ATTRIBUTE => ['required', 'boolean', new HeldGrantRule($this->holdsEditGrants(), $this->alreadyGranted(APC::GRANTS_MOVE))],
		];
	}

	/**
	 * The permission named by the request, or null. {@see IntegerIDRule}
	 * refuses any id that is not an integer.
	 */
	private function findStoredPermission(): ?AccessPermission
	{
		$id = $this->input(RequestAttribute::PERMISSION_ID);

		return is_int($id) ? AccessPermission::with(['album', 'user', 'user_group'])->find($id) : null;
	}

	/**
	 * Whether the stored permission already carries this grant, so that
	 * resending it is keeping it, not handing it out (Q-118-09).
	 * It counts only for the album's owner: anyone else gets the same answer
	 * whatever the stored grants, and learns nothing about them.
	 */
	private function alreadyGranted(string $grant): bool
	{
		/** @var User|null $user */
		$user = Auth::user();
		$owner_id = $this->stored_perm?->album?->owner_id;

		return $user !== null && $owner_id === $user->id && $this->stored_perm?->getAttribute($grant) === true;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var int $id */
		$id = $values[RequestAttribute::PERMISSION_ID];
		$this->perm = $this->stored_perm ?? AccessPermission::with(['album', 'user', 'user_group'])->findOrFail($id);

		$this->perm_resource = new AccessPermissionResource(
			grants_edit: static::toBoolean($values[RequestAttribute::GRANTS_EDIT_ATTRIBUTE]),
			grants_delete: static::toBoolean($values[RequestAttribute::GRANTS_DELETE_ATTRIBUTE]),
			grants_download: static::toBoolean($values[RequestAttribute::GRANTS_DOWNLOAD_ATTRIBUTE]),
			grants_full_photo_access: static::toBoolean($values[RequestAttribute::GRANTS_FULL_PHOTO_ACCESS_ATTRIBUTE]),
			grants_upload: static::toBoolean($values[RequestAttribute::GRANTS_UPLOAD_ATTRIBUTE]),
			grants_move: static::toBoolean($values[RequestAttribute::GRANTS_MOVE_ATTRIBUTE]),
		);
	}
}