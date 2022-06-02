<?php

namespace App\Http\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAbstractAlbum;
use App\Http\Requests\Contracts\HasAlbum;
use App\Http\Requests\Contracts\HasDescription;
use App\Http\Requests\Contracts\HasLicense;
use App\Http\Requests\Contracts\HasPhotos;
use App\Http\Requests\Contracts\HasTags;
use App\Http\Requests\Contracts\HasTitle;
use App\Http\Requests\Traits\HasAlbumTrait;
use App\Http\Requests\Traits\HasDescriptionTrait;
use App\Http\Requests\Traits\HasLicenseTrait;
use App\Http\Requests\Traits\HasPhotosTrait;
use App\Http\Requests\Traits\HasTagsTrait;
use App\Http\Requests\Traits\HasTitleTrait;
use App\Models\Album;
use App\Models\Photo;
use App\Rules\DescriptionRule;
use App\Rules\LicenseRule;
use App\Rules\RandomIDListRule;
use App\Rules\RandomIDRule;
use App\Rules\TitleRule;

class PatchPhotoRequest extends BaseApiRequest implements HasPhotos, HasDescription, HasLicense, HasTitle, HasTags, HasAlbum
{
	use HasPhotosTrait;
	use HasDescriptionTrait;
	use HasLicenseTrait;
	use HasTitleTrait;
	use HasTagsTrait;
	use HasAlbumTrait;

	public const IS_PUBLIC_ATTRIBUTE = 'is_public';

	protected ?bool $isPublic = null;

	public const IS_STARRED_ATTRIBUTE = 'is_starred';

	protected ?bool $isStarred = null;

	protected bool $albumSet = false;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		if ($this->albumSet()) {
			return $this->authorizePhotosWrite($this->photos) &&
				$this->authorizeAlbumWrite($this->album);
		}

		return $this->authorizePhotosWrite($this->photos);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasPhotos::PHOTO_IDS_ATTRIBUTE => ['required', new RandomIDListRule()],
			HasPhotos::PHOTO_IDS_ATTRIBUTE . '.*' => ['required', new RandomIDRule(false)],
			HasDescription::DESCRIPTION_ATTRIBUTE => [new DescriptionRule()],
			HasLicense::LICENSE_ATTRIBUTE => [new LicenseRule()],
			self::IS_PUBLIC_ATTRIBUTE => 'boolean',
			HasTitle::TITLE_ATTRIBUTE => [new TitleRule()],
			self::IS_STARRED_ATTRIBUTE => 'boolean',
			HasTags::TAGS_ATTRIBUTE => 'array',
			HasTags::TAGS_ATTRIBUTE . '.*' => 'string|min:1',
			HasAbstractAlbum::ALBUM_ID_ATTRIBUTE => [new RandomIDRule(true)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		// TODO this does not work if trying to send `false` as boolean value
		$this->photos = Photo::query()->findOrFail(explode(',', $values[HasPhotos::PHOTO_IDS_ATTRIBUTE]));
		$this->description = array_key_exists(HasDescription::DESCRIPTION_ATTRIBUTE, $values) ? $values[HasDescription::DESCRIPTION_ATTRIBUTE] : null;
		$this->license = array_key_exists(HasLicense::LICENSE_ATTRIBUTE, $values) ? $values[HasLicense::LICENSE_ATTRIBUTE] : null;
		$this->isPublic = array_key_exists(self::IS_PUBLIC_ATTRIBUTE, $values) ? static::toBoolean($values[self::IS_PUBLIC_ATTRIBUTE]) : null;
		$this->title = array_key_exists(HasTitle::TITLE_ATTRIBUTE, $values) ? $values[HasTitle::TITLE_ATTRIBUTE] : null;
		$this->isStarred = array_key_exists(self::IS_STARRED_ATTRIBUTE, $values) ? static::toBoolean($values[self::IS_STARRED_ATTRIBUTE]) : null;
		$this->tags = array_key_exists(HasTags::TAGS_ATTRIBUTE, $values) ? $values[HasTags::TAGS_ATTRIBUTE] : null;
		$this->albumSet = array_key_exists(HasAbstractAlbum::ALBUM_ID_ATTRIBUTE, $values);
		if ($this->albumSet) {
			$targetAlbumID = $values[HasAbstractAlbum::ALBUM_ID_ATTRIBUTE];
			$this->album = empty($targetAlbumID) ?
				null :
				Album::query()->findOrFail($targetAlbumID);
		}
	}

	public function isPublic(): ?bool
	{
		return $this->isPublic;
	}

	public function isStarred(): ?bool
	{
		return $this->isStarred;
	}

	public function albumSet(): bool
	{
		return $this->albumSet;
	}
}
