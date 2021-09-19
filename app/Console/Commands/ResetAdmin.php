<?php

namespace App\Console\Commands;

use App\Console\Commands\Utilities\Colorize;
use App\Contracts\ExternalLycheeException;
use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\UnexpectedException;
use App\Legacy\Legacy;
use App\Models\Configs;
use App\Models\User;
use Illuminate\Console\Command;
use Symfony\Component\Console\Exception\ExceptionInterface as SymfonyConsoleException;

class ResetAdmin extends Command
{
	/**
	 * Add color to the command line output.
	 */
	private Colorize $col;

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:reset_admin';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Reset Login and Password of the admin user.';

	/**
	 * Create a new command instance.
	 *
	 * @throws SymfonyConsoleException
	 */
	public function __construct(Colorize $colorize)
	{
		parent::__construct();
		$this->col = $colorize;
	}

	/**
	 * Execute the console command.
	 *
	 * @return int
	 *
	 * @throws ExternalLycheeException
	 */
	public function handle(): int
	{
		try {
			Legacy::resetAdmin();

			// delete to avoid collisions.
			User::query()->where('username', '=', '')->delete();
			User::query()->where('password', '=', '')->delete();
			User::query()->where('id', '=', 0)->delete();

			// recreate an admin user
			$user = new User();
			$user->username = Configs::get_value('username', '');
			$user->password = Configs::get_value('password', '');
			$user->save();

			// created user will have a id which is NOT 0.
			// we want this user to have an ID of 0 as it is the ADMIN ID.
			$user->id = 0;
			$user->save();

			$this->line($this->col->yellow('Admin username and password reset.'));

			return 0;
		} catch (QueryBuilderException $e) {
			throw new UnexpectedException($e);
		}
	}
}
