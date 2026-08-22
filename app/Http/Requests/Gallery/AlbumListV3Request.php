<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Gallery;

use App\Http\Requests\BaseApiRequest;
use App\Models\User;
use App\Rules\BooleanRule;
use Illuminate\Support\Facades\Auth;

/**
 * Request for GET /api/v3/Albums (Feature 057, DO-057-03).
 *
 * `with_parent_id` and `for_bulk_edit` are both independent, admin-gated
 * opt-in flags (Q-057-02): a non-admin caller supplying either as `true` is
 * forbidden (FR-057-02/03/04), while the default mode (both `false`) is open
 * to any visitor/user, matching {@see \App\Policies\AlbumQueryPolicy::applyVisibilityFilter()}'s
 * own null-user support.
 */
class AlbumListV3Request extends BaseApiRequest
{
	private bool $with_parent_id = false;
	private bool $for_bulk_edit = false;

	public function withParentId(): bool
	{
		return $this->with_parent_id;
	}

	public function forBulkEdit(): bool
	{
		return $this->for_bulk_edit;
	}

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		if (!$this->with_parent_id && !$this->for_bulk_edit) {
			return true;
		}

		/** @var User|null $user */
		$user = Auth::user();

		return $user?->may_administrate === true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			'with_parent_id' => ['sometimes', new BooleanRule()],
			'for_bulk_edit' => ['sometimes', new BooleanRule()],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->with_parent_id = static::toBoolean($values['with_parent_id'] ?? false);
		$this->for_bulk_edit = static::toBoolean($values['for_bulk_edit'] ?? false);
	}
}
