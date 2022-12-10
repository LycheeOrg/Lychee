<?php

namespace App\DTO;

use App\Exceptions\Handler as ExceptionHandler;
use App\Models\Logs;

class ImportEventReport extends BaseImportReport
{
	public const REPORT_TYPE = 'event';

	protected string $subtype;
	protected ?string $path;
	protected int $severity;
	protected string $message;
	protected ?\Throwable $throwable;

	protected function __construct(string $subtype, int $severity, ?string $path, string $message, ?\Throwable $throwable = null)
	{
		parent::__construct(self::REPORT_TYPE);
		$this->subtype = $subtype;
		$this->severity = $severity;
		$this->path = $path;
		$this->message = $message;
		$this->throwable = $throwable;
	}

	public static function createWarning(string $subtype, ?string $path, string $message): self
	{
		return new self($subtype, Logs::SEVERITY_WARNING, $path, $message);
	}

	public static function createFromException(\Throwable $e, ?string $path): self
	{
		return new self(class_basename($e), ExceptionHandler::getLogSeverity($e), $path, $e->getMessage(), $e);
	}

	public function getException(): ?\Throwable
	{
		return $this->throwable;
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return array_merge(parent::toArray(), [
			'subtype' => $this->subtype,
			'severity' => Logs::SEVERITY_2_STRING[$this->severity],
			'path' => $this->path,
			'message' => $this->message,
		]);
	}

	public function toCLIString(): string
	{
		return $this->path . ($this->path !== null ? ': ' : '') . $this->message;
	}
}
