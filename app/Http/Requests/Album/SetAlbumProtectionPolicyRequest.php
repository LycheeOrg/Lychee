<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasAbstractAlbum;
use App\Contracts\Http\Requests\HasPassword;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAbstractAlbumTrait;
use App\Http\Requests\Traits\HasPasswordTrait;
use App\Http\Resources\Models\Utils\AlbumProtectionPolicy;
use App\Policies\AlbumPolicy;
use App\Rules\AlbumIDRule;
use App\Rules\PasswordRule;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class SetAlbumProtectionPolicyRequest extends BaseApiRequest implements HasAbstractAlbum, HasPassword
{
	use HasAbstractAlbumTrait;
	use HasPasswordTrait;

	protected bool $isPasswordProvided;
	protected AlbumProtectionPolicy $albumProtectionPolicy;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		if ($this->album instanceof BaseSmartAlbum) {
			return Auth::user()?->may_administrate === true;
		}

		return Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $this->album]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new AlbumIDRule(false)],
			RequestAttribute::PASSWORD_ATTRIBUTE => ['sometimes', new PasswordRule(true)],
			RequestAttribute::IS_PUBLIC_ATTRIBUTE => 'required|boolean',
			RequestAttribute::IS_LINK_REQUIRED_ATTRIBUTE => 'required|boolean',
			RequestAttribute::IS_NSFW_ATTRIBUTE => 'required|boolean',
			RequestAttribute::GRANTS_DOWNLOAD_ATTRIBUTE => 'required|boolean',
			RequestAttribute::GRANTS_FULL_PHOTO_ACCESS_ATTRIBUTE => 'required|boolean',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album = $this->albumFactory->findAbstractAlbumOrFail(
			$values[RequestAttribute::ALBUM_ID_ATTRIBUTE]
		);
		$this->albumProtectionPolicy = new AlbumProtectionPolicy(
			is_public: static::toBoolean($values[RequestAttribute::IS_PUBLIC_ATTRIBUTE]),
			is_link_required: static::toBoolean($values[RequestAttribute::IS_LINK_REQUIRED_ATTRIBUTE]),
			is_nsfw: static::toBoolean($values[RequestAttribute::IS_NSFW_ATTRIBUTE]),
			grants_full_photo_access: static::toBoolean($values[RequestAttribute::GRANTS_FULL_PHOTO_ACCESS_ATTRIBUTE]),
			grants_download: static::toBoolean($values[RequestAttribute::GRANTS_DOWNLOAD_ATTRIBUTE]),
		);
		$this->isPasswordProvided = array_key_exists(RequestAttribute::PASSWORD_ATTRIBUTE, $values);
		$this->password = $this->isPasswordProvided ? $values[RequestAttribute::PASSWORD_ATTRIBUTE] : null;
	}

	/**
	 * @return AlbumProtectionPolicy
	 */
	public function albumProtectionPolicy(): AlbumProtectionPolicy
	{
		return $this->albumProtectionPolicy;
	}

	public function isPasswordProvided(): bool
	{
		return $this->isPasswordProvided;
	}
}
