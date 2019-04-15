<?php


namespace App\Http\Controllers;

use App\Configs;
use App\Metadata\GitHubFunctions;
use App\Response;
use Exception;

/**
 * Class UpdateController
 * @package App\Http\Controllers
 */
class UpdateController extends Controller
{

	private $gitHubFunctions;

	/**
	 * @param GitHubFunctions $gitHubFunctions
	 */
	public function __construct(GitHubFunctions $gitHubFunctions)
	{
		$this->gitHubFunctions = $gitHubFunctions;
	}

	/**
	 * This requires a php to have a shell access.
	 * This method execute the update (git pull).
	 *
	 * @return array|string
	 */
	public function do()
	{

		if (Configs::get_value('allow_online_git_pull', '0') == '0') {
			return Response::error('Online updates are not allowed.');
		}
		if (!function_exists('exec')) {
			return Response::error('exec is not available');
		}
		if (!is_executable('../.git')) {
			return Response::error('../.git is not executable, check the permissions');
		}


		try{
			$up_to_date = $this->gitHubFunctions->is_up_to_date();
		}
		catch (Exception $e)
		{
			return Response::error($e->getMessage());
		}

		if ($up_to_date)
		{
			$output = [];
			chdir('../');
			$command = 'git pull https://github.com/LycheeOrg/Lychee-Laravel.git master 2>&1';
			exec($command, $output, $ret);
			$command = 'php artisan migrate';
			exec($command, $output, $ret);
			return $output;
		}
		else
		{
			return Response::json('Already up to date');
		}
	}
}