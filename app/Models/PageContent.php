<?php

namespace App\Models;

use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\PageContent.
 *
 * @property int         $id
 * @property int         $page_id
 * @property string      $content
 * @property string      $class
 * @property string      $type
 * @property int         $order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|PageContent newModelQuery()
 * @method static Builder|PageContent newQuery()
 * @method static Builder|PageContent query()
 * @method static Builder|PageContent whereClass($value)
 * @method static Builder|PageContent whereContent($value)
 * @method static Builder|PageContent whereCreatedAt($value)
 * @method static Builder|PageContent whereId($value)
 * @method static Builder|PageContent whereOrder($value)
 * @method static Builder|PageContent wherePageId($value)
 * @method static Builder|PageContent whereType($value)
 * @method static Builder|PageContent whereUpdatedAt($value)
 */
class PageContent extends Model
{
	/**
	 * Return content.
	 * It can be an image -> create a img tag, `content` is the url of the image
	 * It can be a div -> create a div tag, `content` is then compiled from Markdown to HTML.
	 *
	 * @return string
	 */
	public function get_content()
	{
		$return = '';
		if ($this->type == 'img') {
			$return = '<div class="' . $this->class . '"><img src="' . $this->content . '" alt="image" /></div>';
		} elseif ($this->type == 'div') {
			$return = '<div class="' . $this->class . '">';
			$return .= Markdown::convertToHtml($this->content);
			$return .= '</div>';
		}

		return $return;
	}
}
