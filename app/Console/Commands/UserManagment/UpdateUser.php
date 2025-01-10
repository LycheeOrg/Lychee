<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Console\Commands\UserManagment;

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
		'{username : username of the user} ' .
		'{password : password of the user} ';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Update the user with the given username.';

	/**
	 * Execute the console command.
	 *
	 * @return int
	 *
	 * @throws ExternalLycheeException
	 */
	public function handle(): int
	{
		$username = strval($this->argument('username'));

		/** @var User|null $user */
		$user = User::query()->where('username', '=', $username)->first();

		if ($user === null) {
			$this->error('user not found');

			return 1;
		}

		$password = strval($this->argument('password'));

		if ($password !== '') {
			$user->password = Hash::make($password);
			$user->save();

			$this->line('Successfully updated user ' . $username);

			return 0;
		}

		$this->error('wrong password');

		return 1;
	}
}
