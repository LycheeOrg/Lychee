<?php

namespace App\Console\Commands;

use App\Contracts\Exceptions\ExternalLycheeException;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class UpdateUser extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature =
		'lychee:update_user' .
		'{--username= : username of the user} ' .
		'{--password= : password of the user} ';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Update the user with the given username. If an option is not set, it is not changed.';

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

		if ($username === null || $username === '') {
			$this->error('Username is missing.');

			return 1;
		}

		/** @var User $user */
		$user = User::query()->where('username', '=', $username)->firstOrFail();

		/** @var string|null $password */
		$password = $this->option('password');

		if ($password !== null && $password !== '') {
			$user->password = Hash::make($password);
		}

		$user->save();

		$this->line('Successfully updated user ' . $username);

		return 0;
	}
}
