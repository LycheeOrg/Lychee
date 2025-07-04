<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\DTO;

class ImportProgressReport extends BaseImportReport
{
	public const REPORT_TYPE = 'progress';

	protected string $path;
	protected int $progress;

	protected function __construct(string $path, int $progress)
	{
		parent::__construct(self::REPORT_TYPE);
		$this->path = $path;
		$this->progress = $progress;
	}

	public static function create(string $path, int $progress): self
	{
		return new self($path, $progress);
	}

	public function toCLIString(): string
	{
		return '<fg=#999>' . $this->path . ': ' . $this->progress . '%</>';
	}
}
