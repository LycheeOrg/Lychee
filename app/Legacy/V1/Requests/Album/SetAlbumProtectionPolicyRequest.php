<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Http\Resources\Models\Utils\AlbumProtectionPolicy;
use App\Legacy\V1\Contracts\Http\Requests\HasBaseAlbum;
use App\Legacy\V1\Contracts\Http\Requests\HasPassword;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Legacy\V1\Requests\Traits\Authorize\AuthorizeCanEditAlbumTrait;
use App\Legacy\V1\Requests\Traits\HasBaseAlbumTrait;
use App\Legacy\V1\Requests\Traits\HasPasswordTrait;
use App\Rules\PasswordRule;
use App\Rules\RandomIDRule;

final class SetAlbumProtectionPolicyRequest extends BaseApiRequest implements HasBaseAlbum, HasPassword
{
	use HasBaseAlbumTrait;
	use HasPasswordTrait;
	use AuthorizeCanEditAlbumTrait;

	protected bool $isPasswordProvided;
	protected AlbumProtectionPolicy $albumProtectionPolicy;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
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
		$this->album = $this->albumFactory->findBaseAlbumOrFail(
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
