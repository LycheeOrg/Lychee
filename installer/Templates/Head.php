<?php

namespace Installer\Templates;

use Template;

class Head implements Template
{
	/**
	 * @var array list of the steps (ordered)
	 */
	private $steps = ['Welcome', 'Requirements', 'Permissions', 'Env', 'Migrate'];

	private function get_index($step)
	{
		return array_search($step, $this->steps);
	}

	public function print(array $input = [])
	{
		echo '<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Lychee Installer</title>
        <link rel="icon" type="image/png" href="installer/assets/img/favicon/favicon-16x16.png" sizes="16x16"/>
        <link rel="icon" type="image/png" href="installer/assets/img/favicon/favicon-32x32.png" sizes="32x32"/>
        <link rel="icon" type="image/png" href="installer/assets/img/favicon/favicon-96x96.png" sizes="96x96"/>
        <link href="installer/assets/css/style.css" rel="stylesheet"/>
        
        <!-- @yield(\'style\') -->
    </head>
    <body>
        <div class="master">
            <div class="box">
                <div class="header">
                    <h1 class="header__title">' . $input['title'] . '</h1>
                </div>
                <ul class="step">
                    <li class="step__divider"></li>
                    <li class="step__item ' . ($input['step'] == 'Migrate' ? 'active' : '')
			. '"  title="Creating the Database">';
		echo '<i class="step__icon fa fa-server" aria-hidden="true"></i>';
		echo '
                    </li>';
		echo '
                    <li class="step__divider"></li>
                    <li class="step__item ' . ($input['step'] == 'Env' ? 'active' : '')
			. '"  title="Setting the environment">';
		if ($this->get_index($input['step']) >= 3) {
			echo '<a href="?step=env">
                                <i class="step__icon fa fa-cog" aria-hidden="true"></i>
                            </a>';
		} else {
			echo '<i class="step__icon fa fa-cog" aria-hidden="true"></i>';
		}
		echo '</li>
                    <li class="step__divider"></li>
                    <li class="step__item ' . ($input['step'] == 'Permissions' ? 'active'
				: '') . '"  title="Checking Permissions">';

		if ($this->get_index($input['step']) >= 2) {
			echo '<a href="?step=perm"><i class="step__icon fa fa-key" aria-hidden="true"></i></a>';
		} else {
			echo '<i class="step__icon fa fa-key" aria-hidden="true"></i>';
		}
		echo '
                    </li>
                    <li class="step__divider"></li>
                    <li class="step__item ' . ($input['step'] == 'Requirements' ? 'active'
				: '') . '" title="Checking Requirements">';

		if ($this->get_index($input['step']) >= 1) {
			echo '<a href="?step=req"><i class="step__icon fa fa-list" aria-hidden="true"></i></a>';
		} else {
			echo '<i class="step__icon fa fa-list" aria-hidden="true"></i>';
		}

		echo '</li>
                    <li class="step__divider"></li>
                    <li class="step__item ' . ($input['step'] == 'Welcome' ? 'active' : '')
			. '"  title="Welcome!">';
		echo '<a href="?step="><i class="step__icon fa fa-home" aria-hidden="true"></i></a>';

		echo '
                    </li>
                    <li class="step__divider"></li>
                </ul>
                <div class="main">';

		if (isset($input['errors'])) {
			echo '
		            <p class="alert alert-danger text-center">
		                <strong>Please fix the errors before going to the next step.</strong>
		            </p>';
		}
	}
}