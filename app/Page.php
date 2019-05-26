<?php

namespace App;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Page.
 *
 * @property int                      $id
 * @property string                   $title
 * @property string                   $menu_title
 * @property int                      $in_menu
 * @property int                      $enabled
 * @property string                   $link
 * @property int                      $order
 * @property Carbon|null              $created_at
 * @property Carbon|null              $updated_at
 * @property Collection|PageContent[] $content
 *
 * @method static Builder|Page enabled()
 * @method static Builder|Page menu()
 * @method static Builder|Page newModelQuery()
 * @method static Builder|Page newQuery()
 * @method static Builder|Page query()
 * @method static Builder|Page whereCreatedAt($value)
 * @method static Builder|Page whereEnabled($value)
 * @method static Builder|Page whereId($value)
 * @method static Builder|Page whereInMenu($value)
 * @method static Builder|Page whereLink($value)
 * @method static Builder|Page whereMenuTitle($value)
 * @method static Builder|Page whereOrder($value)
 * @method static Builder|Page whereTitle($value)
 * @method static Builder|Page whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Page extends Model
{
	/**
	 * Return the relationship between a page and its content.
	 *
	 * @return HasMany
	 */
	public function content()
	{
		return $this->hasMany('App\PageContent', 'page_id', 'id')->orderBy('order', 'ASC');
	}

	/**
	 * Define some scopes.
	 */

	/**
	 * @param $query
	 *
	 * @return mixed
	 */
	public function scopeMenu(Builder $query)
	{
		return $query->where('in_menu', true)->where('enabled', true)->orderBy('order', 'ASC');
	}

	/**
	 * @param $query
	 *
	 * @return mixed
	 */
	public function scopeEnabled(Builder $query)
	{
		return $query->where('enabled', true)->orderBy('order', 'ASC');
	}
}
