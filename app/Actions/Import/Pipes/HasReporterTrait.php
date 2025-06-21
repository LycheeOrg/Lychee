<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Import\Pipes;

use App\DTO\BaseImportReport;
use App\DTO\ImportEventReport;
use App\Exceptions\Handler;

trait HasReporterTrait
{
	/**
	 * Output status update to stdout.
	 *
	 * The output is either sent to a web-client via {@link StreamedResponse}
	 * or to the CLI.
	 *
	 * @param BaseImportReport $report the report
	 *
	 * @return void
	 */
	final protected function report(BaseImportReport $report): void
	{
		echo $report->toCLIString() . PHP_EOL;

		if ($report instanceof ImportEventReport && $report->getException() !== null) {
			Handler::reportSafely($report->getException());
		}
	}
}