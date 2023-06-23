<?php

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasAbstractAlbum;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Exceptions\PasswordRequiredException;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAbstractAlbumTrait;
use App\Http\RuleSets\Album\BasicAlbumIdRuleSet;
use App\Models\Extensions\BaseAlbum;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Gate;

class GetAlbumRequest extends BaseApiRequest implements HasAbstractAlbum
{
	use HasAbstractAlbumTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		$result = Gate::check(AlbumPolicy::CAN_ACCESS, [AbstractAlbum::class, $this->album]);

		// In case of a password protected album, we must throw an exception
		// with a special error message ("Password required") such that the
		// front-end shows the password dialog if a password is set, but
		// does not show the dialog otherwise.
		if (
			!$result &&
			$this->album instanceof BaseAlbum &&
			$this->album->public_permissions()?->password !== null
		) {
			throw new PasswordRequiredException();
		}

		return $result;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return BasicAlbumIdRuleSet::rules();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album = $this->albumFactory->findAbstractAlbumOrFail($values[RequestAttribute::ALBUM_ID_ATTRIBUTE]);
	}
}
