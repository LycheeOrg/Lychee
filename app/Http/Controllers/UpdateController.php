<?php


namespace App\Http\Controllers;

use App\Response;

/**
 * Class UpdateController
 * @package App\Http\Controllers
 */
class UpdateController extends Controller
{

	/**
	 * This requires a php to have a shell access.
	 * This method execute the update (git pull).
	 *
	 * @return array|string
	 */
	public function do()
	{
		if (function_exists('exec')) {

			chdir('../');
			$command = 'git pull';
			$output = [];
			exec($command, $output, $ret);
			if ($ret == 1) {
				return $output;
			}
			else {
				$output[] = 'something went wrong';
				return $output;
			}
		}
		else {
			return Response::warning('exec is not available');
		}
	}
}