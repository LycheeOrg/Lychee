<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Rules;

use App\Enum\SmartAlbumType;
use App\Models\BaseAlbumImpl;
use Illuminate\Contracts\Validation\ValidationRule;
use function Safe\preg_match;

final class SlugRule implements ValidationRule
{
	use ValidateTrait;

	private const SLUG_REGEX = '/^[a-z][a-z0-9_-]{1,249}$/';

	private string $failure_message = '';

	public function __construct(
		private ?string $exclude_album_id = null,
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	public function passes(string $attribute, mixed $value): bool
	{
		if ($value === null || $value === '') {
			return true;
		}

		if (!is_string($value)) {
			$this->failure_message = ':attribute must be a string.';

			return false;
		}

		if (preg_match(self::SLUG_REGEX, $value) !== 1) {
			$this->failure_message = ':attribute must be lowercase, start with a letter, and contain only letters, numbers, hyphens, and underscores (2–250 characters).';

			return false;
		}

		if ($this->isReserved($value)) {
			$this->failure_message = ':attribute is reserved and cannot be used as a slug.';

			return false;
		}

		if ($this->isDuplicate($value)) {
			$this->failure_message = ':attribute is already in use by another album.';

			return false;
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function message(): string
	{
		return $this->failure_message;
	}

	private function isReserved(string $value): bool
	{
		return in_array($value, SmartAlbumType::values(), true);
	}

	private function isDuplicate(string $value): bool
	{
		$query = BaseAlbumImpl::query()->where('slug', '=', $value);

		if ($this->exclude_album_id !== null) {
			$query->where('id', '!=', $this->exclude_album_id);
		}

		return $query->exists();
	}
}
