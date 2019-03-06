<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Markdown;

class PageContent extends Model
{
	function get_content()
	{

		$return = '';
		if ($this->type == 'img') {
			$return = '<div class="'.$this->class.'"><img src="'.$this->content.'"/></div>';
		}
		elseif ($this->type == 'div') {
			$return = '<div class="'.$this->class.'">';
			$return .= Markdown::convertToHtml($this->content);
			$return .= '</div>';
		}

		return $return;

	}
}
