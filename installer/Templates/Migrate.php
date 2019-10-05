<?php


namespace Installer\Templates;


use Template;

class Migrate implements Template
{

	public function print(array $input = [])
	{
		echo '<pre><code>';
		foreach ($input['lines'] as $line)
		{
			echo $line."\n";
		}
		echo '</code></pre>';
	}
}