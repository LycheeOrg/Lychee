<?php

namespace App\Http\Requests\Album;

use App\DTO\AlbumProtectionPolicy;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAbstractAlbum;
use App\Http\Requests\Contracts\HasBaseAlbum;
use App\Http\Requests\Contracts\HasPassword;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditAlbumTrait;
use App\Http\Requests\Traits\HasBaseAlbumTrait;
use App\Http\Requests\Traits\HasPasswordTrait;
use App\Rules\PasswordRule;
use App\Rules\RandomIDRule;

class SetAlbumProtectionPolicyRequest extends BaseApiRequest implements HasBaseAlbum, HasPassword
{
	use HasBaseAlbumTrait;
	use HasPasswordTrait;
	use AuthorizeCanEditAlbumTrait;

	public const IS_NSFW_ATTRIBUTE = 'is_nsfw';
	public const IS_PUBLIC_ATTRIBUTE = 'is_public';
	public const IS_LINK_REQUIRED_ATTRIBUTE = 'is_link_required';
	public const GRANTS_DOWNLOAD_ATTRIBUTE = 'grants_download';
	public const GRANTS_FULL_PHOTO_ACCESS_ATTRIBUTE = 'grants_full_photo_access';

	protected bool $isPasswordProvided;
	protected AlbumProtectionPolicy $albumAccessSettings;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAbstractAlbum::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			HasPassword::PASSWORD_ATTRIBUTE => ['sometimes', new PasswordRule(true)],
			self::IS_PUBLIC_ATTRIBUTE => 'required|boolean',
			self::IS_LINK_REQUIRED_ATTRIBUTE => 'required|boolean',
			self::IS_NSFW_ATTRIBUTE => 'required|boolean',
			self::GRANTS_DOWNLOAD_ATTRIBUTE => 'required|boolean',
			self::GRANTS_FULL_PHOTO_ACCESS_ATTRIBUTE => 'required|boolean',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album = $this->albumFactory->findBaseAlbumOrFail(
			$values[HasAbstractAlbum::ALBUM_ID_ATTRIBUTE]
		);
		$this->albumAccessSettings = new AlbumProtectionPolicy(
			is_public: static::toBoolean($values[self::IS_PUBLIC_ATTRIBUTE]),
			is_link_required: static::toBoolean($values[self::IS_LINK_REQUIRED_ATTRIBUTE]),
			is_nsfw: static::toBoolean($values[self::IS_NSFW_ATTRIBUTE]),
			grants_full_photo_access: static::toBoolean($values[self::GRANTS_FULL_PHOTO_ACCESS_ATTRIBUTE]),
			grants_download: static::toBoolean($values[self::GRANTS_DOWNLOAD_ATTRIBUTE]),
		);
		$this->isPasswordProvided = array_key_exists(HasPassword::PASSWORD_ATTRIBUTE, $values);
		$this->password = $this->isPasswordProvided ? $values[HasPassword::PASSWORD_ATTRIBUTE] : null;
	}

	/**
	 * @return AlbumProtectionPolicy
	 */
	public function albumProtectionPolicy(): AlbumProtectionPolicy
	{
		return $this->albumAccessSettings;
	}

	public function isPasswordProvided(): bool
	{
		return $this->isPasswordProvided;
	}
}
