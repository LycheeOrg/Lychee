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
			$return = '<img class="'.$this->class.'" src="'.$this->content.'"/>';
		}
		elseif ($this->type == 'div') {
			$return = '<div class="'.$this->class.'">';
			$return .= Markdown::convertToHtml($this->content);
			$return .= '</div>';
		}

		return $return;

	}
}
