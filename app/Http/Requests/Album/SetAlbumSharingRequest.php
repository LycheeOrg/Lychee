<?php

namespace App\Http\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbumID;
use App\Http\Requests\Contracts\HasPassword;
use App\Http\Requests\Traits\HasAlbumIDTrait;
use App\Rules\PasswordRule;
use App\Rules\RandomIDRule;

class SetAlbumSharingRequest extends BaseApiRequest implements HasAlbumID
{
	use HasAlbumIDTrait;

	public const IS_PUBLIC_ATTRIBUTE = 'is_public';
	public const REQUIRES_LINK_ATTRIBUTE = 'requires_link';
	public const IS_NSFW_ATTRIBUTE = 'is_nsfw';
	public const IS_DOWNLOADABLE_ATTRIBUTE = 'is_downloadable';
	public const IS_SHARE_BUTTON_VISIBLE_ATTRIBUTE = 'is_share_button_visible';
	public const GRANTS_FULL_PHOTO_ATTRIBUTE = 'grants_full_photo';

	protected array $shareSettings = [];

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizeAlbumWrite([$this->albumID]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAlbumID::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			HasPassword::PASSWORD_ATTRIBUTE => ['sometimes', new PasswordRule(true)],
			self::IS_PUBLIC_ATTRIBUTE => 'required|boolean',
			self::REQUIRES_LINK_ATTRIBUTE => 'required|boolean',
			self::IS_NSFW_ATTRIBUTE => 'required|boolean',
			self::IS_DOWNLOADABLE_ATTRIBUTE => 'required|boolean',
			self::IS_SHARE_BUTTON_VISIBLE_ATTRIBUTE => 'required|boolean',
			self::GRANTS_FULL_PHOTO_ATTRIBUTE => 'required|boolean',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->albumID = $values[HasAlbumID::ALBUM_ID_ATTRIBUTE] ?? null;
		$this->shareSettings = [
			self::IS_PUBLIC_ATTRIBUTE => static::toBoolean($values[self::IS_PUBLIC_ATTRIBUTE]),
			self::REQUIRES_LINK_ATTRIBUTE => static::toBoolean($values[self::REQUIRES_LINK_ATTRIBUTE]),
			self::IS_NSFW_ATTRIBUTE => static::toBoolean($values[self::IS_NSFW_ATTRIBUTE]),
			self::IS_DOWNLOADABLE_ATTRIBUTE => static::toBoolean($values[self::IS_DOWNLOADABLE_ATTRIBUTE]),
			self::IS_SHARE_BUTTON_VISIBLE_ATTRIBUTE => static::toBoolean($values[self::IS_SHARE_BUTTON_VISIBLE_ATTRIBUTE]),
			self::GRANTS_FULL_PHOTO_ATTRIBUTE => static::toBoolean($values[self::GRANTS_FULL_PHOTO_ATTRIBUTE]),
		];
		if (array_key_exists(HasPassword::PASSWORD_ATTRIBUTE, $values)) {
			$this->shareSettings[HasPassword::PASSWORD_ATTRIBUTE] = $values[HasPassword::PASSWORD_ATTRIBUTE];
		}
	}

	/**
	 * @return array{is_public: bool, requires_link: bool, is_nsfw: bool, is_downloadable: bool, is_share_button_visible: bool, grant_full_photo: bool, password: ?string}
	 */
	public function shareSettings(): array
	{
		return $this->shareSettings;
	}
}
