<?php

namespace App\Data\Reports;

use App\Enum\SeverityType;
use App\Exceptions\Handler as ExceptionHandler;

class ImportEventReport extends BaseImportReport
{
	public const REPORT_TYPE = 'event';

	protected function __construct(
		public string $subtype,
		public SeverityType $severity,
		public ?string $path,
		public string $message,
		protected ?\Throwable $throwable = null)
	{
		parent::__construct(self::REPORT_TYPE);
	}

	public static function createWarning(string $subtype, ?string $path, string $message): self
	{
		return new self($subtype, SeverityType::WARNING, $path, $message);
	}

	public static function createFromException(\Throwable $e, ?string $path): self
	{
		return new self(class_basename($e), ExceptionHandler::getLogSeverity($e), $path, $e->getMessage(), $e);
	}

	public function getException(): ?\Throwable
	{
		return $this->throwable;
	}

	public function toCLIString(): string
	{
		return $this->path . ($this->path !== null ? ': ' : '') . $this->message;
	}
}
