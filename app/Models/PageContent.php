<?php

namespace App\Models;

use App\Models\Extensions\ThrowsConsistentExceptions;
use App\Models\Extensions\UseFixedQueryBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Markdown;
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
 */
class PageContent extends Model
{
	use ThrowsConsistentExceptions;
	use UseFixedQueryBuilder;

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
			$return .= Markdown::parse($this->content)->toHtml();
			$return .= '</div>';
		}

		return $return;
	}
}
