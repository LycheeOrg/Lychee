<?php

namespace App\Console\Commands;

use App\Actions\User\Create;
use App\Contracts\ExternalLycheeException;
use App\Models\User;
use Illuminate\Console\Command;

class CreateUser extends Command
{
	/**
	 * Add color to the command line output.
	 */
	private Create $create;

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature =
		'lychee:create_user' .
		'{--username= : username of the new user} ' .
		'{--password= : password of the new user} ' .
		'{--may-edit-own-settings : user can edit own settings}  ' .
		'{--may-upload : user may upload} ' .
		'{--may-administrate : user is an admin} ';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a new user with the given username and password. If no user exists yet, this user will be an admin.';

	/**
	 * Create a new command instance.
	 */
	public function __construct(Create $create)
	{
		parent::__construct();
		$this->create = $create;
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
		/** @var string|null $username */
		$username = $this->option('username');
		/** @var string|null $password */
		$password = $this->option('password');

		if ($username === null || $username === '') {
			$this->error('Username is missing.');

			return 1;
		}
		if ($password === null || $password === '') {
			$this->error('Password is missing.');

			return 1;
		}

		$count = User::query()->count();

		$mayAdministrate = $count < 1 || $this->option('may-administrate') === true;
		$mayEditOwnSettings = $mayAdministrate || $this->option('may-edit-own-settings') === true;
		$mayUpload = $mayAdministrate || $this->option('may-upload') === true;

		$user = $this->create->do($username, $password, $mayUpload, $mayEditOwnSettings);
		$user->may_administrate = $mayAdministrate;
		$user->save();

		$this->line('Successfully created user ' . $username);

		return 0;
	}
}
