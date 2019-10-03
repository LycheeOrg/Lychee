<?php /** @noinspection PhpUnused */


namespace Installer\Templates;

use Template;

class Permissions implements Template
{

	public function print(array $input = [])
	{
		echo "\t".'<ul class="list">'."\n";
		foreach ($input['permissions'] as $permission) {
			echo "\t"."\t".'<li class="list__item list__item--permissions '
				.($permission['isSet'] ? 'success' : 'error').'">'."\n";
			echo "\t"."\t".$permission['folder']."\n";
			echo "\t"."\t".'<span>';
			echo '<i class="fa fa-fw fa-';
			echo $permission['isSet'] ? 'check-circle-o' : 'exclamation-circle';
			echo '"></i>';
			echo $permission['permission'];
			echo '</span>'."\n";
			echo "\t"."\t".'</li>'."\n";
		}
		echo "\t".'</ul>'."\n";

		if (!isset($permissions['errors'])) {
			echo "\t".'<div class="buttons" >'."\n";
			echo "\t"."\t"
				.'<a href = "?step=env" class="button" >Next <i class="fa fa-angle-right fa-fw" aria-hidden="true"></i>'
				."\n";
			echo "\t"."\t".'</a>'."\n";
			echo "\t".'</div>'."\n";
		}
	}
}