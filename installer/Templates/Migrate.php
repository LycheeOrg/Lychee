<?php

namespace Installer\Templates;

use Template;

class Migrate implements Template
{
	public function print(array $input = [])
	{
		echo '<pre><code>';
		foreach ($input['lines'] as $line) {
			echo $line . "\n";
		}
		echo '</code></pre>';

		if (!isset($input['errors'])) {
			echo '<strong>We did not detect any errors. However if the migration failed,
			remove the installed.log file and reopen <a href="install.php">this page</a>.</strong>';
		} else {
			echo "\t" . '<div class="buttons" >' . "\n";
			echo "\t\t"
				. '<a class="button" href="?step=migrate"><i class="fa fa-refresh" aria-hidden="true" > Re-try</i>';
			echo "\t\t" . '</a>';
			echo "\t" . '</div>';
		}
	}
}