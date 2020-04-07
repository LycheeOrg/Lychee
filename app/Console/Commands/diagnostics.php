<?php

namespace App\Console\Commands;

use App\Http\Controllers\DiagnosticsController;
use App\Metadata\DiskUsage;
use App\Metadata\GitHubFunctions;
use App\ModelFunctions\ConfigFunctions;
use App\ModelFunctions\SessionFunctions;
use Illuminate\Console\Command;

class diagnostics extends Command
{
	/**
	 * @var ConfigFunctions
	 */
	private $configFunctions;

	/**
	 * @var GitHubFunctions
	 */
	private $gitHubFunctions;

	/**
	 * @var SessionFunctions
	 */
	private $sessionFunctions;

	/**
	 * @var DiskUsage
	 */
	private $diskUsage;

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
		GitHubFunctions $gitHubFunctions,
		SessionFunctions $sessionFunctions,
		DiskUsage $diskUsage
	) {
		parent::__construct();

		$this->configFunctions = $configFunctions;
		$this->gitHubFunctions = $gitHubFunctions;
		$this->sessionFunctions = $sessionFunctions;
		$this->diskUsage = $diskUsage;
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$ctrl = new DiagnosticsController($this->configFunctions, $this->gitHubFunctions, $this->sessionFunctions, $this->diskUsage);

		$this->line('');
		$this->line('');

		$this->line($this->cyan('Diagnostics'));
		$this->line($this->cyan('-----------'));
		foreach ($ctrl->get_errors() as $err) {
			$err = str_replace('Error: ', $this->red('Error: '), $err);
			$err = str_replace('Warning: ', $this->yellow('Warning: '), $err);
			$this->line($err);
		}
		$this->line('');
		$this->line($this->cyan('System Information'));
		$this->line($this->cyan('------------------'));
		foreach ($ctrl->get_info() as $info) {
			$info = str_replace('Error: ', $this->red('Error: '), $info);
			$info = str_replace('Warning: ', $this->yellow('Warning: '), $info);
			$this->line($info);
		}
		$this->line('');
		$this->line($this->cyan('Config Information'));
		$this->line($this->cyan('------------------'));
		foreach ($ctrl->get_config() as $config) {
			$config = str_replace('Error: ', $this->red('Error: '), $config);
			$config = str_replace('Warning: ', $this->yellow('Warning: '), $config);
			$this->line($config);
		}
	}

	private function color_type($type)
	{
		switch ($type) {
			case 'error  ':
				return $this->red($type);
			case 'warning':
				return $this->yellow($type);
			case 'notice ':
				return $this->cyan($type);
			default:
				return $type;
		}
	}

	private function red($string)
	{
		return '<fg=red>' . $string . '</>';
	}

	private function magenta($string)
	{
		return '<fg=magenta>' . $string . '</>';
	}

	private function green($string)
	{
		return '<fg=green>' . $string . '</>';
	}

	private function yellow($string)
	{
		return '<fg=yellow>' . $string . '</>';
	}

	private function cyan($string)
	{
		return '<fg=cyan>' . $string . '</>';
	}

	private function blue($string)
	{
		return '<fg=blue>' . $string . '</>';
	}
}