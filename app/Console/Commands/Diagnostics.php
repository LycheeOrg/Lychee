<?php

namespace App\Console\Commands;

use App\Actions\Diagnostics\Configuration;
use App\Actions\Diagnostics\Errors;
use App\Actions\Diagnostics\Info;
use App\Console\Commands\Utilities\Colorize;
use Illuminate\Console\Command;

class Diagnostics extends Command
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
	protected $signature = 'lychee:diagnostics';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Show the diagnostics informations.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct(
		Colorize $colorize
	) {
		parent::__construct();

		$this->col = $colorize;
	}

	/**
	 * Format the block.
	 */
	private function block(string $str, array $array)
	{
		$this->line($this->col->cyan($str));
		$this->line($this->col->cyan(str_pad('', strlen($str), '-')));

		foreach ($array as $elem) {
			$elem = str_replace('Error: ', $this->col->red('Error: '), $elem);
			$elem = str_replace('Warning: ', $this->col->yellow('Warning: '), $elem);
			$this->line($elem);
		}
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$this->line('');
		$this->line('');
		$this->block('Diagnostics', resolve(Errors::class)->get());
		$this->line('');
		$this->block('System Information', resolve(Info::class)->get());
		$this->line('');
		$this->block('Config Information', resolve(Configuration::class)->get());
	}
}
