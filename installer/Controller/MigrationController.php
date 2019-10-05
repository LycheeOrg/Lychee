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

		$this->installed($output);
		return ['lines' => $output];
	}



	/**
	 * @return string
	 */
	public function view()
	{
		return 'Migrate';
	}



	/**
	 * @param  array  $output
	 */
	public function installed(array &$output)
	{
		$dateStamp = date('Y/m/d h:i:sa');
		$message = 'Lychee INSTALLED on '.$dateStamp;
		file_put_contents('installed.log', $message);
		$output[] = $message;
	}
}