<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Photo;

use App\Contracts\Http\Requests\HasAlbum;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditAlbumTrait;
use App\Http\Requests\Traits\HasAlbumTrait;
use App\Models\Album;
use App\Policies\AlbumPolicy;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Gate;

class FromUrlRequest extends BaseApiRequest implements HasAlbum
{
	use HasAlbumTrait;
	use AuthorizeCanEditAlbumTrait;

	/** @var string[] */
	protected array $urls;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(AlbumPolicy::CAN_UPLOAD, [AbstractAlbum::class, $this->album]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['present', new RandomIDRule(true)],
			RequestAttribute::URLS_ATTRIBUTE => 'required|array|min:1',
			RequestAttribute::URLS_ATTRIBUTE . '.*' => 'required|string',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album = null;
		/** @var string|null $id */
		$id = $values[RequestAttribute::ALBUM_ID_ATTRIBUTE];
		if ($id !== null) {
			$this->album = Album::query()->findOrFail($id);
		}
		$this->urls = $values[RequestAttribute::URLS_ATTRIBUTE];

		// The replacement below looks suspicious.
		// If it was really necessary, then there would be much more special
		// characters (for example umlauts in international domain names)
		// which would require replacement by their corresponding %-encoding.
		// However, I assume that the PHP method `fopen` is happily fine with
		// any character and internally handles special characters itself.
		// Hence, either use a proper encoding method here instead of our
		// home-brewed, poor-man replacement or drop it entirely.
		// TODO: Find out what is needed and proceed accordingly.
		// ? We can't use URL encode because we need to preserve :// and ?
		$this->urls = str_replace(' ', '%20', $this->urls);
	}

	/**
	 * @return string[]
	 */
	public function urls(): array
	{
		return $this->urls;
	}
}