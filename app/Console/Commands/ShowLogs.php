<?php

namespace App\Console\Commands;

use App\Console\Commands\Utilities\Colorize;
use App\Contracts\Exceptions\ExternalLycheeException;
use App\Contracts\Exceptions\InternalLycheeException;
use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\UnexpectedException;
use App\Models\Logs;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\Console\Exception\ExceptionInterface as SymfonyConsoleException;

class ShowLogs extends Command
{
	/**
	 * Add color to the command line output.
	 */
	private Colorize $col;

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
	 * @throws SymfonyConsoleException
	 */
	public function __construct(Colorize $colorize)
	{
		parent::__construct();
		$this->col = $colorize;
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
		try {
			$action = strval($this->argument('action'));
			$n = (int) $this->argument('n');
			$order = $this->argument('order');

			if ($action === 'clean') {
				Logs::query()->truncate();
				$this->line($this->col->yellow('Log table has been emptied.'));

				return 0;
			}
			// we are in the show part but in the case where 'show' has not been defined.
			// as a results arguments are shifted: n <- action, order <- n.
			elseif ($action !== 'show') {
				$n = (int) $this->argument('action');
				$order = strval($this->argument('n'));
			}
			$this->action_show($n, $order);

			return 0;
		} catch (SymfonyConsoleException|InternalLycheeException $e) {
			throw new UnexpectedException($e);
		}
	}

	/**
	 * @throws QueryBuilderException
	 */
	private function action_show(int $n, string $order): void
	{
		$order = ($order === 'ASC' || $order === 'DESC') ? $order : 'DESC';

		if (Logs::query()->count() === 0) {
			$this->line($this->col->green('Everything looks fine, Lychee has not reported any problems!'));
		} else {
			/** @var Collection<Logs> $logs */
			$logs = Logs::query()
				->orderBy('id', $order)
				->limit($n)
				->get();
			foreach ($logs->reverse() as $log) {
				$this->line($this->col->magenta($log->created_at)
					. ' -- '
					. $this->color_type(str_pad($log->type, 7))
					. ' -- '
					. $this->col->blue($log->function)
					. ' -- '
					. $this->col->green((string) $log->line)
					. ' -- ' . $log->text);
			}
		}
	}

	private function color_type(string $type): string
	{
		return match ($type) {
			'error  ' => $this->col->red($type),
			'warning' => $this->col->yellow($type),
			'notice ' => $this->col->cyan($type),
			default => $type,
		};
	}
}
