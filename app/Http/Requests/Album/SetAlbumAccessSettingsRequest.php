<?php

namespace App\Http\Requests\Album;

use App\DTO\AlbumAccessSettings;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAbstractAlbum;
use App\Http\Requests\Contracts\HasBaseAlbum;
use App\Http\Requests\Contracts\HasPassword;
use App\Http\Requests\Traits\HasBaseAlbumTrait;
use App\Http\Requests\Traits\HasPasswordTrait;
use App\Rules\PasswordRule;
use App\Rules\RandomIDRule;

class SetAlbumAccessSettingsRequest extends BaseApiRequest implements HasBaseAlbum, HasPassword
{
	use HasBaseAlbumTrait;
	use HasPasswordTrait;

	protected bool $isPasswordProvided;
	protected AlbumAccessSettings $albumAccessSettings;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizeAlbumWrite($this->album);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAbstractAlbum::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			HasPassword::PASSWORD_ATTRIBUTE => ['sometimes', new PasswordRule(true)],
			AlbumAccessSettings::IS_PUBLIC_ATTRIBUTE => 'required|boolean',
			AlbumAccessSettings::REQUIRES_LINK_ATTRIBUTE => 'required|boolean',
			AlbumAccessSettings::IS_NSFW_ATTRIBUTE => 'required|boolean',
			AlbumAccessSettings::IS_DOWNLOADABLE_ATTRIBUTE => 'required|boolean',
			AlbumAccessSettings::IS_SHARE_BUTTON_VISIBLE_ATTRIBUTE => 'required|boolean',
			AlbumAccessSettings::GRANTS_FULL_PHOTO_ATTRIBUTE => 'required|boolean',
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
		$this->albumAccessSettings = new AlbumAccessSettings(
			static::toBoolean($values[AlbumAccessSettings::IS_PUBLIC_ATTRIBUTE]),
			static::toBoolean($values[AlbumAccessSettings::REQUIRES_LINK_ATTRIBUTE]),
			static::toBoolean($values[AlbumAccessSettings::IS_NSFW_ATTRIBUTE]),
			static::toBoolean($values[AlbumAccessSettings::IS_DOWNLOADABLE_ATTRIBUTE]),
			static::toBoolean($values[AlbumAccessSettings::IS_SHARE_BUTTON_VISIBLE_ATTRIBUTE]),
			static::toBoolean($values[AlbumAccessSettings::GRANTS_FULL_PHOTO_ATTRIBUTE]),
		);
		$this->isPasswordProvided = array_key_exists(HasPassword::PASSWORD_ATTRIBUTE, $values);
		$this->password = $this->isPasswordProvided ? $values[HasPassword::PASSWORD_ATTRIBUTE] : null;
	}

	/**
	 * @return AlbumAccessSettings
	 */
	public function albumAccessSettings(): AlbumAccessSettings
	{
		return $this->albumAccessSettings;
	}

	public function isPasswordProvided(): bool
	{
		return $this->isPasswordProvided;
	}
}
