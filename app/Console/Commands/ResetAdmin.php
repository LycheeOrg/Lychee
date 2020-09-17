<?php

namespace App\Console\Commands;

use App\Console\Commands\Utilities\Colorize;
use App\Models\Configs;
use Illuminate\Console\Command;

class ResetAdmin extends Command
{
	/**
	 * Add color to the command line output.
	 *
	 * @var Colorize
	 */
	private $col;

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
	 * @return void
	 */
	public function __construct(Colorize $colorize)
	{
		parent::__construct();

		$this->col = $colorize;
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		Configs::where('key', '=', 'username')->orWhere('key', '=', 'password')->update(['value' => '']);
		$this->line($this->col->yellow('Admin username and password reset.'));
	}
}
