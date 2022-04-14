<?php

namespace App\Models;

use App\Exceptions\Internal\QueryBuilderException;
use App\Models\Extensions\FixedQueryBuilder;
use App\Models\Extensions\ThrowsConsistentExceptions;
use App\Models\Extensions\UseFixedQueryBuilder;
use App\Models\Extensions\UTCBasedTimes;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Page.
 *
 * @property int         $id
 * @property string      $title
 * @property string      $menu_title
 * @property int         $in_menu
 * @property int         $enabled
 * @property string      $link
 * @property int         $order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Collection  $content
 *
 * @method static FixedQueryBuilder enabled()
 * @method static FixedQueryBuilder menu()
 * @method static FixedQueryBuilder whereCreatedAt($value)
 * @method static FixedQueryBuilder whereEnabled($value)
 * @method static FixedQueryBuilder whereId($value)
 * @method static FixedQueryBuilder whereInMenu($value)
 * @method static FixedQueryBuilder whereLink($value)
 * @method static FixedQueryBuilder whereMenuTitle($value)
 * @method static FixedQueryBuilder whereOrder($value)
 * @method static FixedQueryBuilder whereTitle($value)
 * @method static FixedQueryBuilder whereUpdatedAt($value)
 */
class Page extends Model
{
	use UTCBasedTimes;
	use ThrowsConsistentExceptions;
	use UseFixedQueryBuilder;

	/**
	 * Return the relationship between a page and its content.
	 *
	 * @return HasMany
	 *
	 * @throws QueryBuilderException
	 */
	public function content(): HasMany
	{
		try {
			return $this
				->hasMany('App\Models\PageContent', 'page_id', 'id')
				->orderBy('order');
		} catch (\InvalidArgumentException $e) {
			throw new QueryBuilderException($e);
		}
	}

	/**
	 * Define some scopes.
	 */

	/**
	 * @param FixedQueryBuilder $query
	 *
	 * @return FixedQueryBuilder
	 *
	 * @throws QueryBuilderException
	 */
	public function scopeMenu(FixedQueryBuilder $query): FixedQueryBuilder
	{
		return $query
			->where('in_menu', true)
			->where('enabled', true)
			->orderBy('order');
	}

	/**
	 * @param FixedQueryBuilder $query
	 *
	 * @return FixedQueryBuilder
	 *
	 * @throws QueryBuilderException
	 */
	public function scopeEnabled(FixedQueryBuilder $query): FixedQueryBuilder
	{
		return $query
			->where('enabled', true)
			->orderBy('order');
	}
}
