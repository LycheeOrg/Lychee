<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasAbstractAlbum;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Exceptions\PasswordRequiredException;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAbstractAlbumTrait;
use App\Models\Configs;
use App\Models\Extensions\BaseAlbum;
use App\Policies\AlbumPolicy;
use App\Rules\AlbumIDRule;
use Illuminate\Support\Facades\Gate;

class GetAlbumRequest extends BaseApiRequest implements HasAbstractAlbum
{
	use HasAbstractAlbumTrait;

    protected int $page = 1;
    protected int $per_page;

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
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new AlbumIDRule(false)],
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:500',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album = $this->album_factory->findAbstractAlbumOrFail($values[RequestAttribute::ALBUM_ID_ATTRIBUTE]);
        $this->page = isset($values['page']) ? (int) $values['page'] : 1;
        $this->per_page = isset($values['per_page']) ? (int) $values['per_page'] : $this->getDefaultPerPage();
	}

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPerPage(): int
    {
        return $this->per_page;
    }

    private function getDefaultPerPage(): int
    {
        if ($this->album instanceof \App\SmartAlbums\UntaggedAlbum) {
            return Configs::getValueAsInt('untagged_photos_pagination_limit');
        }
        return Configs::getValueAsInt('timeline_photos_pagination_limit');
    }
}
