<?php

namespace App\DTO;

class ImportEventReport extends ImportReport
{
	public const REPORT_TYPE = 'event';

	public const SEVERITY_ERROR = 'error';
	public const SEVERITY_WARNING = 'warning';

	protected string $subtype;
	protected ?string $path;
	protected string $severity;
	protected string $message;
	protected ?\Throwable $throwable;

	protected function __construct(string $subtype, string $severity, ?string $path, string $message, ?\Throwable $throwable = null)
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
		return new self($subtype, self::SEVERITY_WARNING, $path, $message);
	}

	public static function createErrorFromException(\Throwable $e, ?string $path): self
	{
		return new self(class_basename($e), self::SEVERITY_ERROR, $path, $e->getMessage(), $e);
	}

	public function isWarning(): bool
	{
		return $this->severity === self::SEVERITY_WARNING;
	}

	public function isError(): bool
	{
		return $this->severity === self::SEVERITY_ERROR;
	}

	public function getException(): ?\Throwable
	{
		return $this->throwable;
	}

	public function getSeverity(): string
	{
		return $this->severity;
	}

	public function getPath(): string
	{
		return $this->path;
	}

	public function getMessage(): string
	{
		return $this->message;
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return array_merge(parent::toArray(), [
			'subtype' => $this->subtype,
			'severity' => $this->severity,
			'path' => $this->path,
			'message' => $this->message,
		]);
	}

	public function toCLIString(): string
	{
		return $this->path . ($this->path ? ': ' : '') . $this->message;
	}
}
