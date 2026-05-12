<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Photo;

use App\Contracts\Http\Requests\HasAlbum;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\DTO\UrlValidatedDTO;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditAlbumTrait;
use App\Http\Requests\Traits\HasAlbumTrait;
use App\Models\Album;
use App\Policies\AlbumPolicy;
use App\Rules\PhotoUrlRule;
use App\Rules\RandomIDRule;
use App\Services\UrlValidation;
use Illuminate\Support\Facades\Gate;

class FromUrlRequest extends BaseApiRequest implements HasAlbum
{
	use HasAlbumTrait;
	use AuthorizeCanEditAlbumTrait;

	/** @var UrlValidatedDTO[] */
	protected array $urls;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(AlbumPolicy::CAN_UPLOAD, [AbstractAlbum::class, $this->album]);
	}

	/**
	 * Prepare the data for validation.
	 *
	 * @return void
	 */
	protected function prepareForValidation(): void
	{
		if ($this->has(RequestAttribute::URLS_ATTRIBUTE) === false) {
			return;
		}

		if (!is_array($this->input(RequestAttribute::URLS_ATTRIBUTE))) {
			return;
		}

		$url_validator = new UrlValidation($this->configs());

		/** @var UrlValidatedDTO[] $urls */
		$urls = [];

		foreach ($this->input(RequestAttribute::URLS_ATTRIBUTE) as $url) {
			$urls[] = $url_validator->validate($url);
		}

		$this->merge([RequestAttribute::URLS_ATTRIBUTE => $urls]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['present', new RandomIDRule(true)],
			RequestAttribute::URLS_ATTRIBUTE => 'required|array|min:1',
			RequestAttribute::URLS_ATTRIBUTE . '.*' => ['required', new PhotoUrlRule()],
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
	}

	/**
	 * @return UrlValidatedDTO[]
	 */
	public function urls(): array
	{
		return $this->urls;
	}
}