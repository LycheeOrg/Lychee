<?php

namespace App\Console\Commands;

use App\Actions\User\ResetAdmin as UserResetAdmin;
use App\Console\Commands\Utilities\Colorize;
use App\Contracts\Exceptions\ExternalLycheeException;
use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\UnexpectedException;
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
	 * Access to the reset admin function.
	 *
	 * @var UserResetAdmin
	 */
	protected UserResetAdmin $userResetAdmin;

	/**
	 * Create a new command instance.
	 *
	 * @throws SymfonyConsoleException
	 */
	public function __construct(Colorize $colorize, UserResetAdmin $userResetAdmin)
	{
		parent::__construct();
		$this->col = $colorize;
		$this->userResetAdmin = $userResetAdmin;
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
			$this->userResetAdmin->do();

			$this->line($this->col->yellow('Admin username and password reset.'));

			return 0;
		} catch (QueryBuilderException $e) {
			throw new UnexpectedException($e);
		}
	}
}
