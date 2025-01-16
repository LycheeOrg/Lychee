<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Console\Commands\UserManagment;

use App\Actions\User\Create;
use App\Contracts\Exceptions\ExternalLycheeException;
use App\Models\User;
use Illuminate\Console\Command;

class CreateUser extends Command
{
	private Create $create;

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature =
		'lychee:create_user' .
		'{username : username of the new user} ' .
		'{password : password of the new user} ' .
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
		$username = strval($this->argument('username'));
		$password = strval($this->argument('password'));

		$count = User::query()->count();

		$mayAdministrate = $count < 1 || $this->option('may-administrate') === true;
		$mayEditOwnSettings = $mayAdministrate || $this->option('may-edit-own-settings') === true;
		$mayUpload = $mayAdministrate || $this->option('may-upload') === true;

		$user = $this->create->do(
			username: $username,
			password: $password,
			mayUpload: $mayUpload,
			mayEditOwnSettings: $mayEditOwnSettings);
		$user->may_administrate = $mayAdministrate;
		$user->save();

		$this->line(sprintf('Successfully created%s user %s ', $mayAdministrate ? ' admin' : '', $username));

		return 0;
	}
}
