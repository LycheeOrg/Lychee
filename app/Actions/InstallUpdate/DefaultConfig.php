<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\InstallUpdate;

use App\Exceptions\Internal\FrameworkException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Container\ContainerExceptionInterface;

class DefaultConfig
{
	/** @var array{core:array<string,string>,requirements:array<string,array<int,string>>,permissions:array<string,string>} */
	private array $config = [
		/*
			|--------------------------------------------------------------------------
			| Server Requirements
			|--------------------------------------------------------------------------
			|
			| This is our Lychee server requirements, we check if the extension is enabled
			| by looping through the array and run "extension_loaded" on it.
			|
			*/
		'core' => ['minPhpVersion' => '8.4.0'],

		'requirements' => [
			'php' => [
				'bcmath', // Required by Laravel
				'ctype', // Required by Laravel
				'dom', // Required by dependencies
				'exif',
				'fileinfo', // Required by Laravel
				'filter', // Required by dependencies
				'gd',
				'json', // Required by Laravel
				'ldap', // Required by dependencies
				'libxml', // Required by dependencies
				'mbstring', // Required by Laravel
				'openssl', // Required by Laravel
				'pcre', // Required by dependencies
				'PDO', // Required by Laravel
				'Phar', // Required by dependencies
				'SimpleXML', // Required by dependencies
				'tokenizer', // Required by Laravel
				'xml', // Required by Laravel
				'xmlwriter', // Required by dependencies
			],
			'apache' => ['mod_rewrite'],
		],
		/*
			|--------------------------------------------------------------------------
			| Folders Permissions
			|--------------------------------------------------------------------------
			|
			| This is the default Lychee folders permissions.
			| you may want to enable more permissions to allow online updates
			|
			*/
		'permissions' => [
			'.' => 'file_exists|is_readable|is_writable|is_executable',
			'database/' => 'file_exists|is_readable|is_writable|is_executable',
			'database/database.sqlite' => 'file_exists|is_readable|is_writable',
			'storage/framework/' => 'file_exists|is_readable|is_writable|is_executable',
			'storage/framework/views/' => 'file_exists|is_readable|is_writable|is_executable',
			'storage/framework/cache/' => 'file_exists|is_readable|is_writable|is_executable',
			'storage/framework/sessions/' => 'file_exists|is_readable|is_writable|is_executable',
			'storage/logs/' => 'file_exists|is_readable|is_writable|is_executable',
			'storage/tmp/extract/' => 'file_exists|is_readable|is_writable|is_executable',
			'storage/tmp/jobs/' => 'file_exists|is_readable|is_writable|is_executable',
			'storage/tmp/uploads/' => 'file_exists|is_readable|is_writable|is_executable',
			'bootstrap/cache/' => 'file_exists|is_readable|is_writable|is_executable',
			'public/dist/' => 'file_exists|is_readable|is_writable|is_executable',
			'public/uploads/' => 'file_exists|is_readable|is_writable|is_executable',
		],
		// This is from https://github.com/rashidlaasri/LaravelInstaller
		// We keep it so we can make the .env edition a bit more friendly (later).
		// This will also allow use to give more details of what each settings in the
		// .env are for.
		//
		//			'environment'  => [
		//				'form' => [
		//					'rules' => [
		//						'app_name'            => 'required|string|max:50',
		//						'environment'         => 'required|string|max:50',
		//						'environment_custom'  => 'required_if:environment,other|max:50',
		//						'app_debug'           => 'required|boolean',
		//						'app_log_level'       => 'required|string|max:50',
		//						'app_url'             => 'required|url',
		//						'database_connection' => 'required|string|max:50',
		//						'database_hostname'   => 'required|string|max:50',
		//						'database_port'       => 'required|numeric',
		//						'database_name'       => 'required|string|max:50',
		//						'database_username'   => 'required|string|max:50',
		//						'database_password'   => 'required|string|max:50',
		// //						'broadcast_driver'    => 'required|string|max:50',
		// //						'cache_driver'        => 'required|string|max:50',
		//						'session_driver'      => 'required|string|max:50',
		// //						'queue_driver'        => 'required|string|max:50',
		// //						'redis_hostname'      => 'required|string|max:50',
		// //						'redis_password'      => 'required|string|max:50',
		// //						'redis_port'          => 'required|numeric',
		// //						'mail_driver'         => 'required|string|max:50',
		// //						'mail_host'           => 'required|string|max:50',
		// //						'mail_port'           => 'required|string|max:50',
		// //						'mail_username'       => 'required|string|max:50',
		// //						'mail_password'       => 'required|string|max:50',
		// //						'mail_encryption'     => 'required|string|max:50',
		// //						'pusher_app_id'       => 'max:50',
		// //						'pusher_app_key'      => 'max:50',
		// //						'pusher_app_secret'   => 'max:50',
		//					],
		//				],
		//			],
	];

	/**
	 * Set the result array permissions and errors.
	 *
	 * @throws FrameworkException
	 */
	public function __construct()
	{
		try {
			$db_possibilities = [
				['mysql', 'mysqli'],
				['mysql', 'pdo_mysql'],
				['pgsql', 'pgsql'],
				['pgsql', 'pdo_pgsql'],
				['sqlite', 'sqlite3'],
			];

			// additional requirement depending on the .env/base config
			foreach ($db_possibilities as $db_possibility) {
				if (config('database.default') === $db_possibility[0]) {
					$this->config['requirements']['php'][] = $db_possibility[1];
				}
			}
			// @codeCoverageIgnoreStart
		} catch (BindingResolutionException|ContainerExceptionInterface $e) {
			throw new FrameworkException('Laravel\'s container component', $e);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * @return array<string,string>
	 */
	public function get_core(): array
	{
		return $this->config['core'];
	}

	/**
	 * @return array<string,array<int,string>>
	 */
	public function get_requirements(): array
	{
		return $this->config['requirements'];
	}

	/**
	 * @return array<string,string>
	 */
	public function get_permissions(): array
	{
		return $this->config['permissions'];
	}
}
