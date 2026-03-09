<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasAbstractAlbum;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Enum\SmartAlbumType;
use App\Factories\AlbumFactory;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAbstractAlbumTrait;
use App\Models\Album;
use App\Models\Tag;
use App\Models\TagAlbum;
use App\Policies\AlbumPolicy;
use App\Rules\AlbumIDRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;

/**
 * Request validator for fetching paginated photos.
 *
 * Validates album_id and optional page parameter.
 */
class GetAlbumPhotosRequest extends BaseApiRequest implements HasAbstractAlbum
{
	use HasAbstractAlbumTrait;

	protected int $page = 1;
	/** @var array<int> */
	protected array $tag_ids = [];
	protected string $tag_logic = 'OR';

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(AlbumPolicy::CAN_ACCESS, [AbstractAlbum::class, $this->album]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new AlbumIDRule(false)],
			RequestAttribute::PAGE_ATTRIBUTE => ['sometimes', 'integer', 'min:1'],
			'tag_ids' => ['sometimes', 'array'],
			'tag_ids.*' => ['integer'],
			'tag_logic' => ['sometimes', 'string', 'in:OR,AND'],
		];
	}

	/**
	 * Configure the validator instance.
	 *
	 * Checks if ALL provided tag IDs are invalid - if so, throw validation error.
	 * Individual invalid tag IDs are silently filtered out in processValidatedValues.
	 */
	public function withValidator(Validator $validator): void
	{
		$validator->after(function ($validator): void {
			$tag_ids = $this->input('tag_ids', []);

			// If tag_ids provided but ALL are invalid (don't exist in database)
			if (count($tag_ids) > 0) {
				$valid_tag_ids = Tag::whereIn('id', $tag_ids)->pluck('id')->toArray();

				// If ALL provided tag IDs are invalid, fail validation
				if (count($valid_tag_ids) === 0) {
					$validator->errors()->add('tag_ids', 'No valid tags found for filtering');
				}
			}
		});
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->page = intval($values[RequestAttribute::PAGE_ATTRIBUTE] ?? 1);

		// Process tag filter parameters
		$tag_ids = $values['tag_ids'] ?? [];
		$this->tag_ids = array_filter($tag_ids, fn ($id) => is_int($id) && $id > 0);
		$this->tag_logic = $values['tag_logic'] ?? 'OR';

		$smart_id = SmartAlbumType::tryFrom($values[RequestAttribute::ALBUM_ID_ATTRIBUTE]);

		if ($smart_id !== null) {
			$this->album = resolve(AlbumFactory::class)->createSmartAlbum($smart_id, true);

			return;
		}

		// Load album without unnecessary relations for this request
		$this->album = Album::without([
			'cover', 'cover.size_variants',
			'min_privilege_cover', 'min_privilege_cover.size_variants',
			'max_privilege_cover', 'max_privilege_cover.size_variants',
			'thumb',
			'owner',
			'statistics',
		])->find($values[RequestAttribute::ALBUM_ID_ATTRIBUTE]);

		// Load tag album if not found as regular album
		$this->album ??= TagAlbum::find($values[RequestAttribute::ALBUM_ID_ATTRIBUTE]);

		// If neither found, throw ModelNotFoundException
		$this->album ??= throw new ModelNotFoundException();
	}

	public function page(): int
	{
		return $this->page;
	}

	/**
	 * Get tag IDs filter.
	 *
	 * @return array<int>
	 */
	public function tagIds(): array
	{
		return $this->tag_ids;
	}

	/**
	 * Get tag logic (OR or AND).
	 */
	public function tagLogic(): string
	{
		return $this->tag_logic;
	}
}
