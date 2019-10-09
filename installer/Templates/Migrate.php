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
		echo '<strong>if the migration failed, remove the installed.log file and reopen the <a href="install.php">this page</a>.</strong>';
	}
}