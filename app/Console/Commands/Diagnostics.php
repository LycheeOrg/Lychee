<?php

namespace App\Console\Commands;

use App\Console\Commands\Utilities\Colorize;
use App\ControllerFunctions\Update\Check as CheckUpdate;
use App\Http\Controllers\DiagnosticsController;
use App\Metadata\DiskUsage;
use App\Metadata\LycheeVersion;
use App\ModelFunctions\ConfigFunctions;
use App\ModelFunctions\SessionFunctions;
use Illuminate\Console\Command;

class Diagnostics extends Command
{
	/**
	 * @var ConfigFunctions
	 */
	private $configFunctions;

	/**
	 * @var LycheeVersion
	 */
	private $lycheeVersion;

	/**
	 * @var SessionFunctions
	 */
	private $sessionFunctions;

	/**
	 * @var DiskUsage
	 */
	private $diskUsage;

	/**
	 * @var CheckUpdate
	 */
	private $checkUpdate;

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
		ConfigFunctions $configFunctions,
		LycheeVersion $lycheeVersion,
		SessionFunctions $sessionFunctions,
		DiskUsage $diskUsage,
		CheckUpdate $checkUpdate,
		Colorize $colorize
	) {
		parent::__construct();

		$this->configFunctions = $configFunctions;
		$this->lycheeVersion = $lycheeVersion;
		$this->sessionFunctions = $sessionFunctions;
		$this->diskUsage = $diskUsage;
		$this->checkUpdate = $checkUpdate;
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
		$ctrl = new DiagnosticsController($this->configFunctions, $this->lycheeVersion, $this->sessionFunctions, $this->diskUsage, $this->checkUpdate);

		$this->line('');
		$this->line('');
		$this->block('Diagnostics', $ctrl->get_errors());
		$this->line('');
		$this->block('System Information', $ctrl->get_info());
		$this->line('');
		$this->block('Config Information', $ctrl->get_config());
	}
}