<?php


namespace Installer\Templates;


use Template;

class Env implements Template
{

	public function print(array $input = [])
	{
		echo '<form method="post" action="?step=env">
        <textarea class="textarea" name="envConfig">'.$input['env'].'</textarea>
        <div class="buttons buttons--right">
            <button class="button button--light" type="submit"><i class="fa fa-floppy-o fa-fw" aria-hidden="true"></i>Save</button>
        </div>
    </form>';

		if(!$input['errors'])
		{
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