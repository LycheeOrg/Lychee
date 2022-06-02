<?php

namespace App\Http\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAbstractAlbum;
use App\Http\Requests\Contracts\HasAlbum;
use App\Http\Requests\Contracts\HasLicense;
use App\Http\Requests\Traits\HasAlbumTrait;
use App\Http\Requests\Traits\HasLicenseTrait;
use App\Models\Album;
use App\Rules\LicenseRule;
use App\Rules\RandomIDRule;

class PatchAlbumRequest extends BaseApiRequest implements HasAlbum, HasLicense
{
	use HasAlbumTrait;
	use HasLicenseTrait;

	public const IS_NSFW_ATTRIBUTE = 'is_nsfw';

	protected bool $isNSFW = false;

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
			HasLicense::LICENSE_ATTRIBUTE => [new LicenseRule()],
			self::IS_NSFW_ATTRIBUTE => 'boolean',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album = Album::query()->findOrFail($values[HasAbstractAlbum::ALBUM_ID_ATTRIBUTE]);
		$this->license = array_key_exists(HasLicense::LICENSE_ATTRIBUTE, $values) ? $values[HasLicense::LICENSE_ATTRIBUTE] : $this->album()->license;
		$this->isNSFW = array_key_exists(self::IS_NSFW_ATTRIBUTE, $values) ? static::toBoolean($values[self::IS_NSFW_ATTRIBUTE]) : $this->album()->is_nsfw;
	}

	public function isNSFW(): bool
	{
		return $this->isNSFW;
	}
}
