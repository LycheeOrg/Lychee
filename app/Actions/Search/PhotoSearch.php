<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Search;

use App\Actions\Search\Strategies\ColourStrategy;
use App\Actions\Search\Strategies\DateStrategy;
use App\Actions\Search\Strategies\FieldLikeStrategy;
use App\Actions\Search\Strategies\PlainTextStrategy;
use App\Actions\Search\Strategies\RatingStrategy;
use App\Actions\Search\Strategies\RatioStrategy;
use App\Actions\Search\Strategies\TagStrategy;
use App\Actions\Search\Strategies\TypeStrategy;
use App\Contracts\Exceptions\InternalLycheeException;
use App\Contracts\Search\PhotoSearchTokenStrategy;
use App\DTO\PhotoSortingCriterion;
use App\DTO\Search\SearchToken;
use App\Models\Album;
use App\Models\Extensions\SortingDecorator;
use App\Models\Photo;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoQueryPolicy;
use App\Repositories\ConfigManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class PhotoSearch
{
	public function __construct(
		protected readonly ConfigManager $config_manager,
		protected PhotoQueryPolicy $photo_query_policy,
	) {
	}

	/**
	 * Apply search directly.
	 *
	 * @param array<int,SearchToken> $tokens
	 *
	 * @return Collection<int,Photo> photos
	 *
	 * @throws InternalLycheeException
	 */
	public function query(array $tokens): Collection
	{
		$query = $this->sqlQuery($tokens);
		$sorting = PhotoSortingCriterion::createDefault();

		return (new SortingDecorator($query))
			->orderBy($sorting->column, $sorting->order)->get();
	}

	/**
	 * Create the query manually.
	 *
	 * @param array<int,SearchToken> $tokens parsed search tokens from {@link SearchTokenParser}
	 * @param Album|null             $album  optional top album used as a search base
	 *
	 * @return Builder<Photo>
	 */
	public function sqlQuery(array $tokens, ?Album $album = null): Builder
	{
		$user = Auth::user();
		$unlocked_album_ids = AlbumPolicy::getUnlockedAlbumIDs();

		$query = $this->photo_query_policy->applySearchabilityFilter(
			query: Photo::query()->with(['albums', 'statistics', 'size_variants', 'palette', 'tags', 'rating']),
			user: $user,
			unlocked_album_ids: $unlocked_album_ids,
			origin: $album,
			include_nsfw: !$this->config_manager->getValueAsBool('hide_nsfw_in_search')
		);

		$strategies = $this->buildStrategyRegistry();

		foreach ($tokens as $token) {
			$strategy = $strategies[$token->modifier ?? ''] ?? $strategies[''];
			$strategy->apply($query, $token);
		}

		return $query;
	}

	/**
	 * Build the map from modifier string (or empty string for plain text) to a strategy instance.
	 *
	 * @return array<string, PhotoSearchTokenStrategy>
	 */
	private function buildStrategyRegistry(): array
	{
		return [
			'' => new PlainTextStrategy(),
			'tag' => new TagStrategy(),
			'date' => new DateStrategy(),
			'type' => new TypeStrategy(),
			'ratio' => new RatioStrategy(),
			'color' => new ColourStrategy($this->config_manager),
			'colour' => new ColourStrategy($this->config_manager),
			'make' => new FieldLikeStrategy('make'),
			'lens' => new FieldLikeStrategy('lens'),
			'aperture' => new FieldLikeStrategy('aperture'),
			'iso' => new FieldLikeStrategy('iso'),
			'shutter' => new FieldLikeStrategy('shutter'),
			'focal' => new FieldLikeStrategy('focal'),
			'title' => new FieldLikeStrategy('title'),
			'description' => new FieldLikeStrategy('description'),
			'location' => new FieldLikeStrategy('location'),
			'model' => new FieldLikeStrategy('model'),
			'rating' => new RatingStrategy(),
		];
	}
}
