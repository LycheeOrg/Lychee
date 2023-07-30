<?php

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasBaseAlbum;
use App\Contracts\Http\Requests\HasPassword;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Data\AlbumProtectionPolicy;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditAlbumTrait;
use App\Http\Requests\Traits\HasBaseAlbumTrait;
use App\Http\Requests\Traits\HasPasswordTrait;
use App\Http\RuleSets\Album\SetAlbumProtectionPolicyRuleSet;

class SetAlbumProtectionPolicyRequest extends BaseApiRequest implements HasBaseAlbum, HasPassword
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
		return SetAlbumProtectionPolicyRuleSet::rules();
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
