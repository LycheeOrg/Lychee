<?php

namespace Installer\Templates;

use Template;

class Env implements Template
{
	public function print(array $input = [])
	{
		echo '
        <ul class="list">
        <li class="list__item list__item--env list__title error"><span><i class="fa fa-ban" aria-hidden="true"></i> <strong>Lychee does not create the database.</strong></span></li>
        <li class="list__item list__item--env">1 - Manually create your database and then enter the sql details bellow.</li>
        <li class="list__item list__item--env">2 - Edit the form below to reflect your desired configuration.</li>
        </ul>
		<strong>For more details of how those values are used, look into "config" folder.</strong><br/>
		<form method="post" action="?step=env">
        <textarea class="textarea" name="envConfig">' . $input['env'] . '</textarea>
        <div class="buttons buttons--right">
            <button class="button button--light" type="submit"><i class="fa fa-floppy-o fa-fw" aria-hidden="true"></i>Save</button>
        </div>
    </form>';

		if ($input['exists'] == true) {
			echo '<div class="buttons-container">
            <a class="button float-right" href="?step=migrate">
                <i class="fa fa-check fa-fw" aria-hidden="true"></i>
                Install
                <i class="fa fa-angle-double-right fa-fw" aria-hidden="true"></i>
            </a>
        </div>';
		}
	}
}