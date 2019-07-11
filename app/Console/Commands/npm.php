<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class npm extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:npm {cmd=compile : the operation to send to npm (start or compile)}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Launch npm on the public/src folder';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$argument = $this->argument('cmd');
		$ret = [];
		if (!file_exists('public/Lychee-front/package-lock.json')) {
			$cmd = 'cd public/Lychee-front; npm install';
			$this->info('execute: ' . $cmd);
			exec($cmd, $ret);
			foreach ($ret as $retline) {
				$this->line($retline);
			}
		}
		if ($argument == 'start') {
			$cmd = 'cd public/Lychee-front; npm start';
		} else {
			$cmd = 'cd public/Lychee-front; npm run compile';
		}
		$this->info('execute: ' . $cmd);
		exec($cmd, $ret);
		foreach ($ret as $retline) {
			$this->line($retline);
		}
	}
}
