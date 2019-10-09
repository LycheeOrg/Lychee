<?php /** @noinspection PhpUnused */


namespace Installer\Templates;

use Template;

class Permissions implements Template
{

	/**
	 * @param  string  $permissions
	 * @param  int  $areSet
	 */
	private function print_permission(string $permissions, int $areSet){
		$array_permission = array_reverse(explode('|', $permissions));
		$ret = array();
		$err = '<i class="fa fa-fw fa-exclamation-circle error"></i>';
		$succ = '<i class="fa fa-fw fa-check-circle-o success"></i>';
		foreach ($array_permission as $perm)
		{
			$ret[] = '<span class="perm">'.($areSet & 1 ? $err : $succ).$perm.'</span>';
			$areSet >>= 1;
		}
		$ret =  join(' ', $ret);
		$ret = str_replace('file_','',$ret);
		$ret = str_replace('!','not',$ret);
		$ret = str_replace('is_',' ',$ret);
		echo $ret;
	}

	public function print(array $input = [])
	{
		echo "\t".'<ul class="list">'."\n";
		foreach ($input['permissions'] as $permission) {
			echo "\t\t".'<li class="list__item list__item--permissions">'."\n";
			echo "\t\t".'<span>'.$permission['folder'].'</span>'."\n";
			$this->print_permission($permission['permission'], $permission['isSet']);
		}
		echo "\t".'</ul>'."\n";

		if (!isset($input['errors'])) {
			echo "\t".'<div class="buttons" >'."\n";
			echo "\t\t"
				.'<a href = "?step=env" class="button" >Next <i class="fa fa-angle-right fa-fw" aria-hidden="true"></i>'
				."\n";
			echo "\t\t".'</a>'."\n";
			echo "\t".'</div>'."\n";
		}
	}
}