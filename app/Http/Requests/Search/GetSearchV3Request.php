<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Search;

use App\Actions\Search\SearchTokenParser;
use App\Contracts\Http\Requests\HasAbstractAlbum;
use App\Contracts\Http\Requests\HasSearchTokens;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\DTO\AlbumSortingCriterion;
use App\DTO\PhotoSortingCriterion;
use App\Enum\OrderSortingType;
use App\Enum\SearchSortingType;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAbstractAlbumTrait;
use App\Http\Requests\Traits\HasSearchTokensTrait;
use App\Models\Album;
use App\Policies\AlbumPolicy;
use App\Rules\Base64EncodedRule;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use function Safe\base64_decode;

/**
 * Shared request for the v3 search family (Feature 069):
 * `GET /api/v3/Search/Photos`, `/Search/albums` and `/Search/albums/rights`.
 * {@see GetSearchV3DetailsRequest} extends it with the `photo_ids[]` scope.
 *
 * Validation and gating mirror v2's {@see GetSearchRequest} exactly (NFR-069-07)
 * with two additions:
 *  - the `features.struct-of-array` flag is checked first, as on every other v3
 *    route (FR-069-10);
 *  - `terms` is validated as decodable base64 rather than being handed to
 *    `base64_decode()` unchecked (FR-069-11) — v2 would pass `false` straight
 *    into the token parser.
 */
class GetSearchV3Request extends BaseApiRequest implements HasAbstractAlbum, HasSearchTokens
{
	use HasAbstractAlbumTrait;
	use HasSearchTokensTrait;

	protected ?PhotoSortingCriterion $photo_sorting_criterion = null;
	protected ?AlbumSortingCriterion $album_sorting_criterion = null;

	public function photoSortingCriterion(): ?PhotoSortingCriterion
	{
		return $this->photo_sorting_criterion;
	}

	public function albumSortingCriterion(): ?AlbumSortingCriterion
	{
		return $this->album_sorting_criterion;
	}

	/**
	 * The search origin, narrowed to a real {@see Album}.
	 *
	 * A smart/tag/person album passed as `album_id` is deliberately treated as
	 * "no origin", exactly as v2's `SearchController::search()` does — those
	 * album kinds have no nested-set `_lft`/`_rgt` bounds to scope a subtree
	 * search by (FR-069-12).
	 */
	public function origin(): ?Album
	{
		return $this->album instanceof Album ? $this->album : null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		if (config('features.struct-of-array') !== true) {
			return false;
		}

		if (!Auth::check() && !$this->configs()->getValueAsBool('search_public')) {
			return false;
		}

		return Gate::check(AlbumPolicy::CAN_ACCESS, [AbstractAlbum::class, $this->album]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::TERM_ATTRIBUTE => ['required', 'string', new Base64EncodedRule()],
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['sometimes', 'nullable', new RandomIDRule(true)],
			RequestAttribute::SORTING_COLUMN_ATTRIBUTE => ['sometimes', 'nullable', new Enum(SearchSortingType::class)],
			RequestAttribute::SORTING_ORDER_ATTRIBUTE => ['required_with:' . RequestAttribute::SORTING_COLUMN_ATTRIBUTE, 'nullable', new Enum(OrderSortingType::class)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album = $this->album_factory->findNullalbleAbstractAlbumOrFail($values[RequestAttribute::ALBUM_ID_ATTRIBUTE] ?? null);

		/** @var string $encoded */
		$encoded = $values[RequestAttribute::TERM_ATTRIBUTE];
		/** @var string $raw base64 validity is already enforced by rules() */
		$raw = base64_decode($encoded, true);
		$this->tokens = SearchTokenParser::parse($raw);

		$sorting_column = SearchSortingType::tryFrom($values[RequestAttribute::SORTING_COLUMN_ATTRIBUTE] ?? '');
		$sorting_order = OrderSortingType::tryFrom($values[RequestAttribute::SORTING_ORDER_ATTRIBUTE] ?? '') ?? OrderSortingType::ASC;

		$this->photo_sorting_criterion = $sorting_column === null
			? null
			: new PhotoSortingCriterion($sorting_column->toPhotoColumn()->toColumnSortingType(), $sorting_order);
		$this->album_sorting_criterion = $sorting_column === null
			? null
			: new AlbumSortingCriterion($sorting_column->toAlbumColumn($sorting_order)->toColumnSortingType(), $sorting_order);
	}
}
