<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\DTO;

use App\Enum\SeverityType;
use App\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;

class ImportEventReport
{
	protected string $subtype;
	protected ?string $path;
	protected SeverityType $severity;
	protected string $message;
	protected ?\Throwable $throwable;

	protected function __construct(string $subtype, SeverityType $severity, ?string $path, string $message, ?\Throwable $throwable = null)
	{
		$this->subtype = $subtype;
		$this->severity = $severity;
		$this->path = $path;
		$this->message = $message;
		$this->throwable = $throwable;
	}

	public static function createWarning(string $subtype, ?string $path, string $message): self
	{
		return new self($subtype, SeverityType::WARNING, $path, $message);
	}

	public static function createError(string $subtype, ?string $path, string $message): self
	{
		return new self($subtype, SeverityType::ERROR, $path, $message);
	}

	public static function createInfo(string $subtype, ?string $path, string $message): self
	{
		return new self($subtype, SeverityType::INFO, $path, $message);
	}

	public static function createNotice(string $subtype, ?string $path, string $message): self
	{
		return new self($subtype, SeverityType::NOTICE, $path, $message);
	}

	public static function createDebug(string $subtype, ?string $path, string $message): self
	{
		return new self($subtype, SeverityType::DEBUG, $path, $message);
	}

	public static function createFromException(\Throwable $e, ?string $path): self
	{
		return new self(class_basename($e), ExceptionHandler::getLogSeverity($e), $path, $e->getMessage(), $e);
	}

	public function getException(): ?\Throwable
	{
		return $this->throwable;
	}

	public function log(): void
	{
		match ($this->severity) {
			SeverityType::EMERGENCY, SeverityType::ALERT, SeverityType::CRITICAL, SeverityType::ERROR => Log::error($this->str()),
			SeverityType::WARNING => Log::warning($this->str()),
			SeverityType::INFO => Log::info($this->str()),
			SeverityType::NOTICE => Log::notice($this->str()),
			SeverityType::DEBUG => Log::debug($this->str()),
			default => Log::info($this->str()), // Default to info for other severities
		};
	}

	public function toCLIString(): string
	{
		$wrapper = match ($this->severity) {
			SeverityType::EMERGENCY, SeverityType::ALERT, SeverityType::CRITICAL, SeverityType::ERROR => '<error>%s</error>',
			SeverityType::WARNING => '<comment>%s</comment>',
			SeverityType::INFO => '<info>%s</info>',
			SeverityType::NOTICE => '<fg=blue>%s</>',
			SeverityType::DEBUG => '<fg=gray>%s</>',
			default => '<info>%s</info>', // Default to info for other severities
		};

		return sprintf($wrapper, $this->str());
	}

	private function str(): string
	{
		return $this->path . ($this->path !== null ? ': ' : '') . $this->message;
	}
}
