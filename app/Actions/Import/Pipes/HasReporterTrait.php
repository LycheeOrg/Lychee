<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Import\Pipes;

use App\DTO\ImportEventReport;
use App\Exceptions\Handler;
use Illuminate\Support\Facades\App;
use Symfony\Component\Console\Output\ConsoleOutput;

trait HasReporterTrait
{
	/**
	 * Output status update to stdout.
	 *
	 * The output is either sent to a web-client via {@link StreamedResponse}
	 * or to the CLI.
	 *
	 * @param ImportEventReport $report the report
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore
	 */
	final protected function report(ImportEventReport $report): void
	{
		// Silence reporting during unit tests
		// to avoid cluttering the test output with reports.
		if (App::runningUnitTests()) {
			return;
		}

		$msg_section = (new ConsoleOutput())->section();
		$msg_section->writeln($report->toCLIString());
		$report->log();

		if ($report instanceof ImportEventReport && $report->getException() !== null) {
			Handler::reportSafely($report->getException());
		}
	}
}