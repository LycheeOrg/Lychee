<?php

namespace App\Actions\Photo;

use App\Eloquent\FixedQueryBuilder;
use App\Enum\ColumnSortingPhotoType;
use App\Enum\OrderSortingType;
use App\Models\Configs;
use App\Models\Photo;
use App\Policies\PhotoQueryPolicy;
use Illuminate\Database\Eloquent\Builder;

class Timeline
{
	protected PhotoQueryPolicy $photoQueryPolicy;

	public function __construct(PhotoQueryPolicy $photoQueryPolicy)
	{
		$this->photoQueryPolicy = $photoQueryPolicy;
	}

	/**
	 * Create the query manually.
	 *
	 * @return FixedQueryBuilder<Photo>
	 */
	public function do(): Builder
	{
		$order = Configs::getValueAsEnum('timeline_photos_order', ColumnSortingPhotoType::class);

		// Safe default (should not be needed).
		// @codeCoverageIgnoreStart
		if (!in_array($order, [ColumnSortingPhotoType::CREATED_AT, ColumnSortingPhotoType::TAKEN_AT], true)) {
			$order = ColumnSortingPhotoType::TAKEN_AT;
		}
		// @codeCoverageIgnoreEnd

		return $this->photoQueryPolicy->applySearchabilityFilter(
			query: Photo::query()->with(['album', 'size_variants', 'size_variants.sym_links']),
			origin: null,
			include_nsfw: !Configs::getValueAsBool('hide_nsfw_in_timeline')
		)->orderBy($order->value, OrderSortingType::DESC->value);
	}
}