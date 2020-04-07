<?php

namespace App\Console\Commands;

use App\Logs;
use Illuminate\Console\Command;

class show_logs extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:logs {action=show : show or clean} {n=100 : number of lines} {order=DESC : ASCending or DESCending}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Print the logs table.';

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
		$action = $this->argument('action');
		$n = (int) $this->argument('n');
		$order = $this->argument('order');

		if ($action == 'clean')
		{
			Logs::truncate();
			$this->line($this->yellow('Log table has been emptied.'));
			return ;
		}
		// we are in the show part but in the case where 'show' has not be defined.
		// as a results arguments are shifted: n <- action, order <- n.
		else if ($action != 'show')
		{
			$n = (int) $this->argument('action');
			$order = $this->argument('n');
		}
		$this->action_show($n, $order);
	}

	private function action_show($n, $order)
	{
		$order = ($order == 'ASC' || $order == 'DESC') ? $order : 'DESC';

		if (Logs::count() == 0) {
			$this->line($this->green('Everything looks fine, Lychee has not reported any problems!'));
		} else {
			$logs = Logs::orderBy('id', $order)->limit($n)->get();
			foreach ($logs->reverse() as $log) {
				$this->line($this->magenta($log->created_at) . ' -- ' . $this->color_type(str_pad($log->type, 7)) . ' -- ' . $this->blue($log->function) . ' -- ' . $this->green($log->line) . ' -- ' . $log->text);
			}
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