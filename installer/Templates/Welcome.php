<?php /** @noinspection PhpUnused */

namespace Installer\Templates;

use Template;

class Welcome implements Template
{

	public function print(array $input = [])
	{
		echo '<p class="text-center">Welcome to Lychee</p>
			    <p class="text-center">
			      <a href="?step=req" class="button">Next 
			        <i class="fa fa-angle-right fa-fw" aria-hidden="true"></i>
			      </a>
			    </p>';
	}
}