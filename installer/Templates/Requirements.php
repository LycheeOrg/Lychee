<?php


namespace Installer\Templates;


use Template;

class Requirements implements Template
{

	public function print(array $input = [])
	{
		echo "<br>";
		foreach (
			$input['requirements'] as $type => $requirement
		) {
			echo "<br>";
			echo "\t".'<ul class="list">';
			echo "\t"."\t".'<li class="list__item list__title ';
			echo $input['phpSupportInfo']['supported'] ? 'success' : 'error';
			echo '">'."\n";
			echo "\t"."\t".'<strong>';
			echo ucfirst($type);
			echo '</strong>'."\n";
			if ($type == 'php') {
				echo "\t"."\t".'<strong>'."\n";
				echo "\t"."\t".'<small>(version ';
				echo $input['phpSupportInfo']['minimum'];
				echo ' required)'."\n";
				echo "\t"."\t".'</small>'."\n";
				echo "\t"."\t".'</strong>'."\n";
				echo "\t"."\t".'<span class="float-right" >'."\n";
				echo "\t"."\t".'<strong>'."\n";
				echo $input['phpSupportInfo']['current'];
				echo "\t"."\t".'</strong>'."\n";
				echo "\t"."\t".'<i class="fa fa-fw fa-';
				echo $input['phpSupportInfo']['supported'] ? 'check-circle-o'
					: 'exclamation-circle';
				echo ' row-icon" aria-hidden= "true"></i></span>'."\n";
			}
			echo "\t"."\t".'</li >'."\n";
			foreach ($requirement as $extention => $enabled) {
				echo "\t"."\t".'<li class="list__item ';
				echo $enabled ? 'success' : 'error';
				echo '" >'."\n";
				echo $extention;
				echo '<i class="fa fa-fw fa-';
				echo $enabled ? 'check-circle-o' : 'exclamation-circle';
				echo ' row-icon" aria-hidden="true"></i>'."\n";
				echo "\t"."\t".'</li>'."\n";
			}
			echo "\t".'</ul >';
		}

		if (!isset($input['errors'])
			&& $input['phpSupportInfo']['supported']
		) {
			echo "\t".'<div class="buttons" >'."\n";
			echo "\t"."\t"
				.'<a class="button" href="?step=perm"> Next<i class="fa fa-angle-right fa-fw" aria-hidden="true" ></i>';
			echo "\t"."\t".'</a>';
			echo "\t".'</div>';
		}
	}
}