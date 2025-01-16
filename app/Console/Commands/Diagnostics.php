<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Console\Commands;

use App\Actions\Diagnostics\Configuration;
use App\Actions\Diagnostics\Errors;
use App\Actions\Diagnostics\Info;
use App\Console\Commands\Utilities\Colorize;
use App\Contracts\Exceptions\ExternalLycheeException;
use App\DTO\DiagnosticData;
use App\Enum\MessageType;
use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\UnexpectedException;
use Illuminate\Console\Command;
use Symfony\Component\Console\Exception\ExceptionInterface as SymfonyConsoleException;

class Diagnostics extends Command
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
	protected $signature = 'lychee:diagnostics
	                {--skip=* : Skip certain diagnostics check, overrides SKIP_DIAGNOSTICS_CHECKS config}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Show diagnostic information.';

	/**
	 * Create a new command instance.
	 *
	 * @throws SymfonyConsoleException
	 */
	public function __construct(
		Colorize $colorize,
	) {
		parent::__construct();

		$this->col = $colorize;
	}

	/**
	 * Format the block.
	 *
	 * @param string        $str
	 * @param array<string> $array
	 */
	private function block(string $str, array $array): void
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
	 * Format the block.
	 *
	 * @param string           $str
	 * @param DiagnosticData[] $array
	 */
	private function blockDiagnostic(string $str, array $array): void
	{
		$this->line($this->col->cyan($str));
		$this->line($this->col->cyan(str_pad('', strlen($str), '-')));

		foreach ($array as $elem) {
			$prefix = match ($elem->type) {
				MessageType::ERROR => $this->col->red('Error: '),
				MessageType::WARNING => $this->col->yellow('Warning: '),
				default => $this->col->green('Info: '),
			};
			$this->line($prefix . $elem->message);
			foreach ($elem->details as $detail) {
				$this->line('         ' . $detail);
			}
		}
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
		/** @var string[] $skip_diagnostics */
		$skip_diagnostics = config('app.skip_diagnostics_checks');
		/** @var string[] $options */
		$options = $this->option('skip');
		if (sizeof($options) > 0) {
			$skip_diagnostics = $options;
		}
		try {
			$this->line('');
			$this->line('');
			$this->blockDiagnostic('Smart Diagnostics', resolve(Errors::class)->get($skip_diagnostics));
			$this->line('');
			$this->block('System Information', resolve(Info::class)->get());
			$this->line('');
			$this->block('Config Information', resolve(Configuration::class)->get());
		} catch (QueryBuilderException $e) {
			throw new UnexpectedException($e);
		}

		return 0;
	}
}
