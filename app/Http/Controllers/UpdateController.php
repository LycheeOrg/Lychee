<?php


namespace App\Http\Controllers;

use App\Configs;
use App\Logs;
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


		try {
			$up_to_date = $this->gitHubFunctions->is_up_to_date();
		}
		catch (Exception $e) {
			return Response::error($e->getMessage());
		}

		if ($up_to_date) {
			$output = [];
			chdir('../');
			$command = 'git pull https://github.com/LycheeOrg/Lychee-Laravel.git master 2>&1';
			exec($command, $output);
			if (env('APP_ENV', 'production') == 'production') {
				if (Configs::get_value('force_migration_in_production') == '1') {
					Logs::warning(__METHOD__,__LINE__,'Force migration is production.');
					$command = 'php artisan migrate --force'; # we use force to also be able to apply it in production environment.
				}
				else {
					$output[] = 'Migration not applied: `APP_ENV` in `.env` is `production` and `force_migration_in_production` is set to `0`.';
					Logs::warning(__METHOD__,__LINE__,'Migration not applied: `APP_ENV` in `.env` is `production` and `force_migration_in_production` is set to `0`.');
				}
			}
			else {
				$command = 'php artisan migrate';
			}
			exec($command, $output);
			return $output;
		}
		else {
			return Response::json('Already up to date');
		}
	}
}