<?php

namespace Installer;

class Config
{
	private $config
		= [
			/*
			|--------------------------------------------------------------------------
			| Server Requirements
			|--------------------------------------------------------------------------
			|
			| This is the default Laravel server requirements, you can add as many
			| as your application require, we check if the extension is enabled
			| by looping through the array and run "extension_loaded" on it.
			|
			*/
			'core' => ['minPhpVersion' => '7.2.0'],
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
			| This is the default Laravel folders permissions, if your application
			| requires more permissions just add them to the array list bellow.
			|
			*/
			'permissions' => [
				'.' => 'file_exists|is_readable|is_writable',
				'storage/framework/' => 'file_exists|is_readable|is_writable',
				'storage/logs/' => 'file_exists|is_readable|is_writable',
				'bootstrap/cache/' => 'file_exists|is_readable|is_writable',
				'public/dist/' => 'file_exists|is_readable|is_writable',
				'public/sym/' => 'file_exists|is_readable|is_writable',
				'public/uploads/' => 'file_exists|is_readable|is_writable',
				'public/uploads/big/' => 'file_exists|is_readable|is_writable',
				'public/uploads/import/' => 'file_exists|is_readable|is_writable',
				'public/uploads/medium/' => 'file_exists|is_readable|is_writable',
				'public/uploads/raw/' => 'file_exists|is_readable|is_writable',
				'public/uploads/small/' => 'file_exists|is_readable|is_writable',
				'public/uploads/thumb/' => 'file_exists|is_readable|is_writable',
			],
			// not used yet.
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