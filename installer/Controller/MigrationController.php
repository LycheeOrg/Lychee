<?php


namespace Installer\Controller;


class MigrationController implements Controller
{

	/**
	 * @return array
	 */
	public function do()
	{
		$output = [];
		exec('php artisan migrate', $output);
		exec('php artisan key:generate', $output);
		return ['artisan' => $output];
	}



	/**
	 * @return string
	 */
	public function view()
	{
		return 'Migrate';
	}
}