<?php

namespace App\Models;

use App\Exceptions\Internal\QueryBuilderException;
use App\Models\Extensions\ThrowsConsistentExceptions;
use App\Models\Extensions\UTCBasedTimes;
use Illuminate\Database\Eloquent\Builder;
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
 * @method static Builder enabled()
 * @method static Builder menu()
 * @method static Builder whereCreatedAt($value)
 * @method static Builder whereEnabled($value)
 * @method static Builder whereId($value)
 * @method static Builder whereInMenu($value)
 * @method static Builder whereLink($value)
 * @method static Builder whereMenuTitle($value)
 * @method static Builder whereOrder($value)
 * @method static Builder whereTitle($value)
 * @method static Builder whereUpdatedAt($value)
 */
class Page extends Model
{
	use UTCBasedTimes;
	use ThrowsConsistentExceptions;

	const FRIENDLY_MODEL_NAME = 'page';

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
	 * @param Builder $query
	 *
	 * @return Builder
	 *
	 * @throws QueryBuilderException
	 */
	public function scopeMenu(Builder $query): Builder
	{
		try {
			return $query
			->where('in_menu', true)
			->where('enabled', true)
			->orderBy('order');
		} catch (\InvalidArgumentException $e) {
			throw new QueryBuilderException($e);
		}
	}

	/**
	 * @param Builder $query
	 *
	 * @return Builder
	 *
	 * @throws QueryBuilderException
	 */
	public function scopeEnabled(Builder $query): Builder
	{
		try {
			return $query
			->where('enabled', true)
			->orderBy('order');
		} catch (\InvalidArgumentException $e) {
			throw new QueryBuilderException($e);
		}
	}

	protected function friendlyModelName(): string
	{
		return self::FRIENDLY_MODEL_NAME;
	}
}
