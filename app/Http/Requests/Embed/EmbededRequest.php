<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Embed;

use App\Contracts\Http\Requests\HasBaseAlbum;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Factories\AlbumFactory;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasBaseAlbumTrait;
use App\Http\Resources\Models\Utils\AlbumProtectionPolicy;

class EmbededRequest extends BaseApiRequest implements HasBaseAlbum
{
	use HasBaseAlbumTrait;

	public ?int $limit = null;
	public int $offset = 0;
	public ?string $sort = null;
	/** @var string[]|null */
	public ?array $authors = null;

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		if ($this->album === null) {
			// Stream case: always allowed
			return true;
		}

		$policy = AlbumProtectionPolicy::ofBaseAlbum($this->album);

		// Must be public and not (require password or link-only access)
		return $policy->is_public &&
			!$policy->is_password_required &&
			!$policy->is_link_required;
	}

	// No validation here.
	public function rules(): array
	{
		return [];
	}

	protected function prepareForValidation(): void
	{
		/** @disregard */
		$this->merge([
			RequestAttribute::ALBUM_ID_ATTRIBUTE => $this->route(RequestAttribute::ALBUM_ID_ATTRIBUTE),
		]);
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		// Parse pagination parameters
		$limit = $this->query('limit', null);
		$offset = $this->query('offset', 0);
		$sort = $this->query('sort', null);

		// Validate and cap limit to 500 max
		if ($limit !== null) {
			$this->limit = max(1, min((int) $limit, 500));
		}
		$this->offset = max(0, (int) $offset);

		// Validate sort order
		if ($sort !== null && !in_array($sort, ['asc', 'desc'], true)) {
			$this->sort = null; // Invalid value, use default
		} else {
			$this->sort = $sort;
		}

		// Parse author filter (supports comma-separated usernames)
		$author = $this->query('author', null);
		if ($author !== null && is_string($author) && $author !== '') {
			$authors = array_filter(array_map('trim', explode(',', $author)), fn ($v) => $v !== '');
			if (count($authors) > 0) {
				$this->authors = array_values($authors);
			}
		}

		$album_id = $this->route(RequestAttribute::ALBUM_ID_ATTRIBUTE, null);
		if ($album_id === null) {
			// Stream case: no album
			return;
		}

		$this->album = resolve(AlbumFactory::class)->findBaseAlbumOrFail($album_id, false);
		$this->album->loadMissing(['access_permissions']);
	}
}
