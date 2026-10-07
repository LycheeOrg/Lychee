<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Insights;

use App\DTO\Insights\InsightsPeriod;
use App\DTO\Insights\InsightsScope;
use App\Enum\InsightsPeriodType;
use App\Http\Requests\BaseApiRequest;
use App\Models\Album;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Request for `GET /api/v3/Insights` (Feature 085, API-085-01).
 *
 * Every user reads their own library; only administrators name another owner
 * (`owner_id`) or every owner (`whole_instance`) (FR-085-04, ADR-085-02).
 * An album tree (`album_id`) is open to its owner and to administrators
 * (FR-085-16).
 */
class GetInsightsRequest extends BaseApiRequest
{
	public const OWNER_ID_ATTRIBUTE = 'owner_id';
	public const WHOLE_INSTANCE_ATTRIBUTE = 'whole_instance';
	public const ALBUM_ID_ATTRIBUTE = 'album_id';
	public const PERIOD_ATTRIBUTE = 'period';
	public const YEAR_ATTRIBUTE = 'year';
	public const FROM_ATTRIBUTE = 'from';
	public const TO_ATTRIBUTE = 'to';

	private ?int $requested_owner_id = null;
	private bool $whole_instance = false;
	private ?string $requested_album_id = null;
	private ?Album $album = null;
	private InsightsPeriod $period;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			self::OWNER_ID_ATTRIBUTE => ['sometimes', 'integer'],
			self::WHOLE_INSTANCE_ATTRIBUTE => ['sometimes', 'boolean'],
			self::ALBUM_ID_ATTRIBUTE => ['sometimes', 'string'],
			self::PERIOD_ATTRIBUTE => ['required', Rule::enum(InsightsPeriodType::class)],
			self::YEAR_ATTRIBUTE => ['required_if:' . self::PERIOD_ATTRIBUTE . ',' . InsightsPeriodType::YEAR->value, 'integer', 'between:1000,9999'],
			self::FROM_ATTRIBUTE => ['required_if:' . self::PERIOD_ATTRIBUTE . ',' . InsightsPeriodType::RANGE->value, 'date_format:Y-m-d'],
			self::TO_ATTRIBUTE => ['required_if:' . self::PERIOD_ATTRIBUTE . ',' . InsightsPeriodType::RANGE->value, 'date_format:Y-m-d', 'after_or_equal:' . self::FROM_ATTRIBUTE],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->requested_owner_id = isset($values[self::OWNER_ID_ATTRIBUTE]) ? intval($values[self::OWNER_ID_ATTRIBUTE]) : null;
		$this->whole_instance = self::toBoolean($values[self::WHOLE_INSTANCE_ATTRIBUTE] ?? false);
		$this->requested_album_id = $values[self::ALBUM_ID_ATTRIBUTE] ?? null;
		$this->period = match (InsightsPeriodType::from($values[self::PERIOD_ATTRIBUTE])) {
			InsightsPeriodType::LIBRARY => InsightsPeriod::library(),
			InsightsPeriodType::YEAR => InsightsPeriod::year(intval($values[self::YEAR_ATTRIBUTE])),
			InsightsPeriodType::RANGE => InsightsPeriod::range($values[self::FROM_ATTRIBUTE], $values[self::TO_ATTRIBUTE]),
		};
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws ValidationException when an administrator names an unknown user
	 */
	public function authorize(): bool
	{
		if (!Auth::check()) {
			return false;
		}

		if ($this->requested_album_id !== null) {
			return $this->authorizeAlbum($this->requested_album_id);
		}

		if (!$this->asksForOtherOwners()) {
			return true;
		}

		if (Auth::user()?->may_administrate !== true) {
			return false;
		}

		if ($this->requested_owner_id !== null && !User::query()->where('id', '=', $this->requested_owner_id)->exists()) {
			throw ValidationException::withMessages([self::OWNER_ID_ATTRIBUTE => 'The selected owner does not exist.']);
		}

		return true;
	}

	public function scope(): InsightsScope
	{
		if ($this->album !== null) {
			return InsightsScope::album($this->album->id, $this->album->_lft, $this->album->_rgt);
		}

		if ($this->whole_instance) {
			return InsightsScope::wholeInstance();
		}

		return InsightsScope::owner($this->requested_owner_id ?? intval(Auth::id()));
	}

	public function period(): InsightsPeriod
	{
		return $this->period;
	}

	/**
	 * Album scope: the album's owner or an administrator (FR-085-16).
	 *
	 * @throws ValidationException when the ID names no regular album
	 */
	private function authorizeAlbum(string $album_id): bool
	{
		$this->album = Album::query()->find($album_id);
		if ($this->album === null) {
			throw ValidationException::withMessages([self::ALBUM_ID_ATTRIBUTE => 'The selected album does not exist.']);
		}

		return Auth::user()?->may_administrate === true || $this->album->owner_id === intval(Auth::id());
	}

	private function asksForOtherOwners(): bool
	{
		return $this->whole_instance || ($this->requested_owner_id !== null && $this->requested_owner_id !== intval(Auth::id()));
	}
}
