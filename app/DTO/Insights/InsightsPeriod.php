<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO\Insights;

use App\Enum\InsightsPeriodType;
use Safe\DateTimeImmutable;

/**
 * Period covered by Insights (Feature 085, DO-085-02): the whole library, or
 * the local capture dates from `from` to `to`, both inclusive.
 *
 * Capture times are stored in UTC, so SQL selects a UTC window widened by the
 * largest timezone offset on each side and the caller keeps the photos whose
 * local date is in the period (ADR-085-03).
 */
final readonly class InsightsPeriod
{
	private const MAX_OFFSET_HOURS = 14;

	private function __construct(
		public InsightsPeriodType $type,
		public ?string $from,
		public ?string $to,
	) {
	}

	public static function library(): self
	{
		return new self(InsightsPeriodType::LIBRARY, null, null);
	}

	public static function year(int $year): self
	{
		return new self(InsightsPeriodType::YEAR, sprintf('%04d-01-01', $year), sprintf('%04d-12-31', $year));
	}

	/**
	 * @param string $from local date `Y-m-d`
	 * @param string $to   local date `Y-m-d`, not before $from
	 */
	public static function range(string $from, string $to): self
	{
		return new self(InsightsPeriodType::RANGE, $from, $to);
	}

	public function isLibrary(): bool
	{
		return $this->type === InsightsPeriodType::LIBRARY;
	}

	/**
	 * Whether a local capture date belongs to the period.
	 */
	public function includes(string $local_date): bool
	{
		return $this->from === null || ($local_date >= $this->from && $local_date <= $this->to);
	}

	/**
	 * UTC bounds of the SQL pre-filter, or null for the whole library.
	 *
	 * @return array{0:string,1:string}|null
	 */
	public function utcWindow(): ?array
	{
		if ($this->from === null || $this->to === null) {
			return null;
		}

		$utc = new \DateTimeZone('UTC');
		$margin = self::MAX_OFFSET_HOURS . ' hours';

		return [
			(new DateTimeImmutable($this->from . ' 00:00:00', $utc))->modify('-' . $margin)->format('Y-m-d H:i:s'),
			(new DateTimeImmutable($this->to . ' 23:59:59', $utc))->modify('+' . $margin)->format('Y-m-d H:i:s'),
		];
	}

	public function cacheKey(): string
	{
		return $this->type->value . ':' . ($this->from ?? '') . ':' . ($this->to ?? '');
	}
}
