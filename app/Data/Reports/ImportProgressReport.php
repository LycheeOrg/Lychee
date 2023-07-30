<?php

namespace App\Data\Reports;

class ImportProgressReport extends BaseImportReport
{
	public const REPORT_TYPE = 'progress';

	protected function __construct(
		public string $path,
		public int $progress)
	{
		parent::__construct(self::REPORT_TYPE);
	}

	public static function create(string $path, int $progress): self
	{
		return new self($path, $progress);
	}

	public function toCLIString(): string
	{
		return $this->path . ': ' . $this->progress . '%';
	}
}
