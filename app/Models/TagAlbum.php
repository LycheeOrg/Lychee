<?php

namespace App\Models;

use App\Contracts\BaseModelAlbum;
use App\Facades\AccessControl;
use App\Models\Extensions\ForwardsToParentImplementation;
use App\Models\Extensions\HasBidirectionalRelationships;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Query\Builder;

/**
 * Class TagAlbum.
 *
 * @property string showtags
 */
class TagAlbum extends Model implements BaseModelAlbum
{
	use HasBidirectionalRelationships;
	use ForwardsToParentImplementation;

	/**
	 * Indicates if the model's primary key is auto-incrementing.
	 *
	 * @var bool
	 */
	public $incrementing = false;

	/**
	 * Returns the relationship between this model and the implementation
	 * of the "parent" class.
	 *
	 * @return MorphOne
	 */
	public function base_class(): MorphOne
	{
		return $this->morphOne(
			BaseAlbumImpl::class,
			BaseAlbumImpl::INHERITANCE_RELATION_NAME,
			BaseAlbumImpl::INHERITANCE_DISCRIMINATOR_COL_NAME,
			BaseAlbumImpl::INHERITANCE_ID_COL_NAME,
			BaseAlbumImpl::INHERITANCE_ID_COL_NAME
		);
	}

	public function getPhotosAttribute(): Collection
	{
		$sql = Photo::query();

		$tags = explode(',', $this->showtags);
		foreach ($tags as $tag) {
			$sql = $sql->where('tags', 'like', '%' . trim($tag) . '%');
		}

		return $sql->where(fn ($q) => $this->filter($q))->get();
	}

	protected function filter(Builder $query): Builder
	{
		if (AccessControl::is_admin()) {
			return $query;
		}

		if (AccessControl::is_logged_in()) {
			$query = $query->where('owner_id', '=', AccessControl::id())
				->orWhereIn('album_id', $this->albumIds);
		} else {
			$query = $query->whereIn('album_id', $this->albumIds);
		}

		if (Configs::get_value('public_photos_hidden', '1') === '0') {
			$query = $query->orWhere('public', '=', 1);
		}

		return $query;
	}
}