<?php

namespace App\Http\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbumID;
use App\Http\Requests\Contracts\HasAlbumModelID;
use App\Http\Requests\Contracts\HasPassword;
use App\Http\Requests\Traits\HasAlbumModelIDTrait;
use App\Http\Requests\Traits\HasPasswordTrait;
use App\Rules\ModelIDRule;
use App\Rules\PasswordRule;

class UnlockAlbumRequest extends BaseApiRequest implements HasAlbumModelID, HasPassword
{
	use HasAlbumModelIDTrait;
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
			HasAlbumID::ALBUM_ID_ATTRIBUTE => ['required', new ModelIDRule(false)],
			HasPassword::PASSWORD_ATTRIBUTE => ['required', new PasswordRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->albumID = intval($values[HasAlbumID::ALBUM_ID_ATTRIBUTE]);
		$this->password = $values[HasPassword::PASSWORD_ATTRIBUTE];
	}
}
