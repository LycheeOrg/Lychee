<?php

namespace App\Http\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbumID;
use App\Http\Requests\Contracts\HasPassword;
use App\Http\Requests\Traits\HasAlbumIDTrait;
use App\Http\Requests\Traits\HasPasswordTrait;
use App\Rules\PasswordRule;
use App\Rules\RandomIDRule;

class UnlockAlbumRequest extends BaseApiRequest implements HasAlbumID, HasPassword
{
	use HasAlbumIDTrait;
	use HasPasswordTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAlbumID::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			HasPassword::PASSWORD_ATTRIBUTE => ['required', new PasswordRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->albumID = $values[HasAlbumID::ALBUM_ID_ATTRIBUTE];
		$this->password = $values[HasPassword::PASSWORD_ATTRIBUTE];
	}
}
