<?php

namespace App\ControllerFunctions\Install;

class DefaultConfig
{
	private $config
	= [
		/*
			|--------------------------------------------------------------------------
			| Server Requirements
			|--------------------------------------------------------------------------
			|
			| This is our Lychee server requirements, we check if the extension is enabled
			| by looping through the array and run "extension_loaded" on it.
			|
			*/
		'core' => ['minPhpVersion' => '7.4.0'],

		'requirements' => [
			'php' => [
				'openssl',
				'pdo',
				'mbstring',
				'tokenizer',
				'JSON',
				'exif',
				'gd',
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
			'storage/framework/' => 'file_exists|is_readable|is_writable|is_executable',
			'storage/framework/views/' => 'file_exists|is_readable|is_writable|is_executable',
			'storage/framework/cache/' => 'file_exists|is_readable|is_writable|is_executable',
			'storage/framework/sessions/' => 'file_exists|is_readable|is_writable|is_executable',
			'storage/logs/' => 'file_exists|is_readable|is_writable|is_executable',
			'bootstrap/cache/' => 'file_exists|is_readable|is_writable|is_executable',
			'public/dist/' => 'file_exists|is_readable|is_writable|is_executable',
			'public/img/' => 'file_exists|is_readable|is_writable|is_executable',
			'public/sym/' => 'file_exists|is_readable|is_writable|is_executable',
			'public/uploads/' => 'file_exists|is_readable|is_writable|is_executable',
			'public/uploads/big/' => 'file_exists|is_readable|is_writable|is_executable',
			'public/uploads/import/' => 'file_exists|is_readable|is_writable|is_executable',
			'public/uploads/medium/' => 'file_exists|is_readable|is_writable|is_executable',
			'public/uploads/raw/' => 'file_exists|is_readable|is_writable|is_executable',
			'public/uploads/small/' => 'file_exists|is_readable|is_writable|is_executable',
			'public/uploads/thumb/' => 'file_exists|is_readable|is_writable|is_executable',
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
		////						'broadcast_driver'    => 'required|string|max:50',
		////						'cache_driver'        => 'required|string|max:50',
		//						'session_driver'      => 'required|string|max:50',
		////						'queue_driver'        => 'required|string|max:50',
		////						'redis_hostname'      => 'required|string|max:50',
		////						'redis_password'      => 'required|string|max:50',
		////						'redis_port'          => 'required|numeric',
		////						'mail_driver'         => 'required|string|max:50',
		////						'mail_host'           => 'required|string|max:50',
		////						'mail_port'           => 'required|string|max:50',
		////						'mail_username'       => 'required|string|max:50',
		////						'mail_password'       => 'required|string|max:50',
		////						'mail_encryption'     => 'required|string|max:50',
		////						'pusher_app_id'       => 'max:50',
		////						'pusher_app_key'      => 'max:50',
		////						'pusher_app_secret'   => 'max:50',
		//					],
		//				],
		//			],
	];

	/**
	 * Set the result array permissions and errors.
	 *
	 * @return mixed
	 */
	public function __construct()
	{
		$db_possibilities = [
			['mysql', 'mysqli'],
			['mysql', 'pdo_mysql'],
			['pgsql', 'pgsql'],
			['pgsql', 'pdo_pgsql'],
			['sqlite', 'sqlite3'],
		];

		// additional requirement depending of the .env/base config
		foreach ($db_possibilities as $db_possibility) {
			if (config('database.default') == $db_possibility[0]) {
				$this->config['requirements']['php'][] = $db_possibility[1];
			}
		}
	}

	public function get_core()
	{
		return $this->config['core'];
	}

	public function get_requirements()
	{
		return $this->config['requirements'];
	}

	public function get_permissions()
	{
		return $this->config['permissions'];
	}
}
